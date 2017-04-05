<?php

namespace Drupal\social_icon\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;
use Drupal\Core\Url;
use Drupal\social_icon\SocialIconStorage;

/**
 * Sample UI to update a record.
 */
class SocialIconUpdateForm extends FormBase {

    /**
     * {@inheritdoc}
     */
    public function getFormId() {
        return 'social_icon_update_form';
    }

    /**
     * update a record.
     */
    public function buildForm(array $form, FormStateInterface $form_state) {
        // Wrap the form in a div.
        $form = array(
            '#prefix' => '<div id="updateform">',
            '#suffix' => '</div>',
        );
        // Add some explanatory text to the form.
        $form['message'] = array(
            '#markup' => $this->t('Database update operation.'),
        );
        // Query for items to display.
        $entries = SocialIconStorage::load();
        // Tell the user if there is nothing to display.
        if (empty($entries)) {
            $form['no_values'] = array(
                '#value' => t('No entries exist in the table social_icon table.'),
            );
            return $form;
        }

        $default_id = (int)\Drupal::routeMatch()->getParameters()->all()['id'];
        $keyed_entries = array();

        foreach ($entries as $entry) {
            $options[$entry->id] = t('@id: @title @url (@icon) (@hover_icon)', array(
                '@id' => $entry->id,
                '@title' => $entry->title,
                '@url' => $entry->url,
                '@icon' => $entry->icon,
                '@hover_icon' => $entry->hover_icon,
            ));
            $keyed_entries[$entry->id] = $entry;
        }

        // Grab the pid.
        $id = $form_state->getValue('id');
        // Use the pid to set the default entry for updating.
        $default_entry = !empty($id) ? $keyed_entries[$id] : $keyed_entries[$default_id];

        // Save the entries into the $form_state. We do this so the AJAX callback
        // doesn't need to repeat the query.
        $form_state->setValue('entries', $keyed_entries);

        $form['id'] = array(
            '#type' => 'select',
            '#options' => $options,
            '#title' => t('Choose entry to update'),
            '#default_value' => $default_entry->id,
            '#ajax' => array(
                'wrapper' => 'updateform',
                'callback' => array($this, 'updateCallback'),
            ),
        );

        $form['title'] = array(
            '#type' => 'textfield',
            '#title' => t('Updated title'),
            '#size' => 15,
            '#default_value' => $default_entry->title,
        );

        $form['url'] = array(
            '#type' => 'textfield',
            '#title' => t('Updated url'),
            '#size' => 15,
            '#default_value' => $default_entry->url,
        );
        $form['icon'] = array(
            '#type' => 'managed_file',
            '#title' => t('update icon'),
            '#size' => 5,
            '#description' => t('Only JPEG, PNG and GIF images are allowed.'),
            '#upload_location' => 'public://upload/',
            '#required' => FALSE,
            '#upload_validators' => array(
                'file_validate_is_image' => array(),
                'file_validate_extensions' => array('png gif jpg jpeg'),
                'file_validate_size' => array(1 * 1024 * 1024),
            ),
            '#default_value' => [$default_entry->icon],
        );

        $form['hover_icon'] = array(
            '#type' => 'managed_file',
            '#title' => t('update hover icon'),
            '#size' => 5,
            '#description' => t('Only JPEG, PNG and GIF images are allowed.'),
            '#upload_location' => 'public://upload/',
            '#required' => FALSE,
            '#upload_validators' => array(
                'file_validate_is_image' => array(),
                'file_validate_extensions' => array('png gif jpg jpeg'),
                'file_validate_size' => array(1 * 1024 * 1024),
            ),
            '#default_value' => [$default_entry->hover_icon],
        );

        $form['submit'] = array(
            '#type' => 'submit',
            '#value' => t('Update'),
        );
        return $form;
    }

    /**
     * AJAX callback handler for the id select.
     *
     * When the id changes, populates the defaults from the database in the form.
     */
    public function updateCallback(array $form, FormStateInterface $form_state) {
        // Gather the DB results from $form_state.
        $entries = $form_state->getValue('entries');
        // Use the specific entry for this $form_state.
        $entry = $entries[$form_state->getValue('id')];
        
        foreach (array('title', 'url', 'icon', 'hover_icon') as $item) {
            $form[$item]['#value'] = $entry->$item;
        }
        return $form;
    }

    /**
     * {@inheritdoc}
     */
    public function validateForm(array &$form, FormStateInterface $form_state) {
        // Confirm that title more 2 characters
        if (2 > strlen($form_state->getValue('title'))) {
            $form_state->setErrorByName('title', $this->t('Title needs to be more 5 characters'));
        }

        if (!preg_match('/^(http|https):\/\//', $form_state->getValue('url'))) {
            $form_state->setErrorByName('url', t('"Url" do not begin "http://" or "https://"'));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state) {

        $icons = array(
            (int)$form_state->getValue('icon')[0],
            (int)$form_state->getValue('hover_icon')[0]
        );

       foreach ($icons as $fid) {
            if($fid > 0){
                $file = File::load($fid);
                if(gettype($file) === 'object') {
                    $file->setPermanent();
                    $file->save();
                }
            }
        }

        // Save the submitted entry.
        $entry = [
            'id' => (int)$form_state->getValue('id'),
            'title' => $form_state->getValue('title'),
            'url' => $form_state->getValue('url'),
            'icon' => (int)$form_state->getValue('icon')[0],
            'hover_icon' => (int)$form_state->getValue('hover_icon')[0]
        ];

        $count = SocialIconStorage::update($entry);

        $url = Url::fromRoute('social_icon.admin_settings');
        $form_state->setRedirectUrl($url);

        drupal_set_message(t('Updated entry @entry (@count row updated)', array(
            '@count' => $count,
            '@entry' => print_r($entry, TRUE),
        )));
    }

}
