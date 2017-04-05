<?php

namespace Drupal\social_icon\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;
use Drupal\Core\Url;
use Drupal\social_icon\SocialIconStorage;

/**
 * Simple form to add an entry, with all the interesting fields.
 */
class SocialIconAddForm extends FormBase {

    /**
     * {@inheritdoc}
     */
    public function getFormId() {
        return 'social_icon_add_form';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state) {
        $form = array();

        $form['message'] = array(
            '#markup' => $this->t('Add an entry to the social_icon table.'),
        );

        $form['add'] = array(
            '#type' => 'fieldset',
            '#title' => t('Add a social icon'),
        );
        $form['add']['title'] = array(
            '#type' => 'textfield',
            '#title' => t('Title'),
            '#size' => 15,
            '#description' => t('Title must consists of letters')
        );
        $form['add']['url'] = array(
            '#type' => 'textfield',
            '#title' => t('url'),
            '#size' => 15,
            '#description' => t('Input link in format "http://" or "https://".')
        );
        $form['add']['icon'] = array(
            '#type' => 'managed_file',
            '#title' => t('icon'),
            '#size' => 5,
            '#description' => t('Only JPEG, PNG and GIF images are allowed.'),
            '#upload_location' => 'public://upload/',
            '#upload_validators' => array(
                'file_validate_is_image' => array(),
                'file_validate_extensions' => array('png gif jpg jpeg'),
                'file_validate_size' => array(1 * 1024 * 1024),
            ),
        );
        $form['add']['hover_icon'] = array(
            '#type' => 'managed_file',
            '#title' => t('hover_icon'),
            '#size' => 5,
            '#description' => t('Only JPEG, PNG and GIF images are allowed.'),
            '#upload_location' => 'public://upload/',
            '#upload_validators' => array(
                'file_validate_is_image' => array(),
                'file_validate_extensions' => array('png gif jpg jpeg'),
                'file_validate_size' => array(1 * 1024 * 1024),
            ),

        );
        $form['add']['submit'] = array(
            '#type' => 'submit',
            '#value' => t('Add'),
        );

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

       // $file = File::loadMultiple($icons);
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
        $entry = array(
            'title' => $form_state->getValue('title'),
            'url' => $form_state->getValue('url'),
            'icon' => (int)$form_state->getValue('icon')[0],
            'hover_icon' => (int)$form_state->getValue('hover_icon')[0],
        );
        $return = SocialIconStorage::insert($entry);

        $url = Url::fromRoute('social_icon.admin_settings');
        $form_state->setRedirectUrl($url);

        if ($return) {
            drupal_set_message(t('Created entry @entry', array('@entry' => print_r($entry, TRUE))));
        }
    }

}
