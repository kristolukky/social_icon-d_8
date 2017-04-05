<?php

namespace Drupal\social_icon\Plugin\Block;


use Drupal\Core\Block\BlockBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\social_icon\SocialIconStorage;
use Drupal\social_icon\Controller\SocialIconController;
use Drupal\Core\Url;
use Drupal\file\Entity\File;

/**
 * Provides a 'SocialIcon' Block.
 *
 * @Block(
 *   id = "social_icon_block",
 *   admin_label = @Translation("Social Icon Block"),
 *   category = @Translation("Custom")
 * )
 *
 */

class SocialIconBlock extends BlockBase {
    /**
     * {@inheritdoc}
     */
    public function defaultConfiguration() {
        return array(
            'width' => 50,
            'height' => 50,
            'position' => 'gorisontal',
        )+ parent::defaultConfiguration();
    }

    /**
     * {@inheritdoc}
     */
    public function blockForm($form, FormStateInterface $form_state) {

        $form = parent::blockForm($form, $form_state);

        $config = $this->getConfiguration();

        $form['width'] = array(
            '#title' => t('Icons width'),
            '#type' => 'textfield',
            '#size' => 3,
            '#description' => t('Default width 50px'),
            '#default_value' => $config['width'],
        );
        $form['height'] = array(
            '#title' => t('Icons height'),
            '#type' => 'textfield',
            '#size' => 3,
            '#description' => t('Default height 50px'),
            '#default_value' => $config['height'],
        );
        $form['position'] = array(
            '#title' => t('Icons position'),
            '#type' => 'select',
            '#options' => array(
                'gorisontal' => t('Gorisontal'),
                'vertical' => t('Vertical')
            ),
            '#size' => 3,
            '#description' => t('Default value "Gorisontal"'),
            '#default_value' => $config['position'],
        );

        return $form;
    }

    /**
     * {@inheritdoc}
     */
    public function blockValidate($form, FormStateInterface $form_state) {
        $width = $form_state->getValue('width');
        $height = $form_state->getValue('height');

        if (!is_numeric($width) || $width < 16) {
            $form_state->setErrorByName('width', t('Needs to be an integer and more or equal 16 px.'));
        }

        if (!is_numeric($height) || $height < 16) {
            $form_state->setErrorByName('height', t('Needs to be an integer and more or equal 16 px.'));
        }

    }

    /**
     * {@inheritdoc}
     */
    public function blockSubmit($form, FormStateInterface $form_state) {
        $this->configuration['width'] = $form_state->getValue('width');
        $this->configuration['height'] = $form_state->getValue('height');
        $this->configuration['position'] = $form_state->getValue('position');

    }

    /**
     * {@inheritdoc}
     */
    public function build() {

       $config = $this->getConfiguration();
       $content = [];
       foreach ($entries = SocialIconStorage::load() as $entry) {

           if (isset($entry->hover_icon)) {
               $file = File::load($entry->hover_icon);
               if (gettype($file) === 'object'){
                    $file = $file->getFileUri();
                    $hover_icon_path = Url::fromUri(file_create_url($file))->toString();
                }
           }

            $content[$entry->id]['id'] = $entry->id;
            $content[$entry->id]['title'] = $entry->title;
            $content[$entry->id]['url'] = $entry->url;
            $content[$entry->id]['icon'] = SocialIconController::_image_array((int)$entry->icon, $entry->title, $config['height'], $config['width'], $hover_icon_path);
            $content[$entry->id]['hover_icon'] = SocialIconController::_image_array((int)$entry->hover_icon, $config['height'], $config['width']);
       }


      $build = [
           '#theme' => 'social_icon',
           '#icons' => $content,
           '#width' => $config['width'],
           '#height' => $config['height'],
           '#position' => $config['position'],
           '#attached' => array(
               'library' => array(
                    'social_icon/social_icon',
               ),
               'drupalSettings' => array(
                    'width' => $config['width'],
                    'height' => $config['height'],
                    'position' => $config['position'],
              ),
          ),
        ];

       $build['#cache']['max-age'] = 0;

       return $build;
    }

    /**
     * {@inheritdoc}
     */
//    protected function blockAccess(AccountInterface $account) {
//        return $account->hasPermission('access content');
//    }
}