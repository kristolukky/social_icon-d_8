<?php

namespace Drupal\social_icon\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use \Drupal\Core\Routing;
use Drupal\file\Entity\File;
use Drupal\Core\Url;
use Drupal\social_icon\SocialIconStorage;

class SocialIconDeleteForm extends FormBase
{

    /**
     * {@inheritdoc}
     */
    public function getFormId()
    {
        return 'social_icon_delete_form';
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
            '#markup' => $this->t('Delete social icon from database.'),
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
            '#title' => t('Choose entry to delete'),
            '#default_value' => $default_entry->id,
            '#ajax' => array(
                'wrapper' => 'updateform',
                'callback' => array($this, 'updateCallback'),
            ),
        );

        $form['submit'] = array(
            '#type' => 'submit',
            '#value' => t('Delete'),
        );
        return $form;
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state) {
        $entry['id'] =  $form_state->getValue('id');
        $count = SocialIconStorage::delete($entry);

        $url = Url::fromRoute('social_icon.admin_settings');
        $form_state->setRedirectUrl($url);

        drupal_set_message(t('Deleted entry @entry (@count row updated)', array(
            '@count' => $count,
            '@entry' => print_r($entry, TRUE),
        )));
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
        // Setting the #value of items is the only way I was able to figure out
        // to get replaced defaults on these items. #default_value will not do it
        // and shouldn't.
        foreach (array('title', 'url', 'icon', 'hover_icon') as $item) {
            $form[$item]['#value'] = $entry->$item;
        }
        return $form;
    }
}