<?php

namespace Drupal\social_icon\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\file\Entity\File;
use Drupal\Core\Render;
use Drupal\Core\Theme;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\social_icon\SocialIconStorage;


/**
 * Controller for Social Icon.
 */
class SocialIconController extends ControllerBase {

  /*
   * fuction for render image theme
   */
    public static function _image_array($fid, $title = null, $height = 50, $width = 50, $hover_icon_path = null) {
        if($fid > 0 && null !== $fid) {
            $file = File::load($fid);

            if(null !== $file) {

               $variables = array(
                   'style_name' => 'thumbnail',
                   'uri' => $file->getFileUri(),
               );

               // The image.factory service will check if our image is valid.
               $image = \Drupal::service('image.factory')->get($file->getFileUri());

                if($height == 50 && $width == 50){
                    $variables['width'] = $variables['height'] = $height;
                }

               if ($height !== 50) {
                   $variables['height'] = $height;
               }

               if ($width !== 50) {
                   $variables['width'] = $width;
               }

               $attributes = array(
                   'class' => 'social-img',
                   'id' => 'social-img',
               );

                if(null !== $hover_icon_path){
                    $attributes = array_merge($attributes, array('data-hover-icon' => $hover_icon_path));
                }

               $title = $title === null ? $file->getFilename(): $title;
               $attributes = null !== $hover_icon_path ? array_merge($attributes, array('data-hover-icon' => $hover_icon_path)) : $attributes;
               $attributes = null !== $title ? array_merge($attributes, array('title' => $title, 'alt' => $title)) : $attributes;

               $social_icon = array(
                   'uri' => $variables['uri'],
                   'width' => $variables['width'],
                   'height' => $variables['height'],
                   'attributes' => $attributes,
               );

               $renderer = \Drupal::service('renderer');
               $renderer->addCacheableDependency($social_icon, $file);

               return \Drupal::theme()->render('image', $social_icon);
           }else{
              return drupal_set_message('File(s) does not exist');
           }
        }else{
            return NULL;
        }
    }

    /**
     * Render a list of entries in the database.
     */
    public function entryList() {
        $content = array();

        $content['message'] = array(
            '#markup' => $this->t('Generate a list of all entries in the database. There is no filter in the query.'),
        );

        $rows = array();
        $headers = array(t('title'), t('url'), t('icon'), t('hover_icon'), t('actions'), '');

        foreach ($entries = SocialIconStorage::load() as $entry) {

            $url_edit = Url::fromRoute('social_icon.social_icon_update', array('id' => $entry->id));
            $url_delete = Url::fromRoute('social_icon.social_icon_delete', array('id' => $entry->id));

            $actions = array(
                Link::fromTextAndUrl(t('edit'), $url_edit),
                Link::fromTextAndUrl(t('delete'), $url_delete),
            );

            $rows[] = array(
                    $entry->title,
                    $entry->url,
                    self::_image_array((int)$entry->icon),
                    self::_image_array((int)$entry->hover_icon),
                    $actions[0],
                    $actions[1]
            );
        }

        $content['table'] = array(
            '#type' => 'table',
            '#header' => $headers,
            '#rows' => $rows,
            '#empty' => t('No entries available.'),
        );

        $url_add = Url::fromRoute('social_icon.social_icon_add');
        $url_add = Link::fromTextAndUrl(t('Add social icon'), $url_add)->toString();

        $content['link'] = array(
            '#markup' => $url_add
        );
        // Don't cache this page.
        $content['#cache']['max-age'] = 0;
       
        return $content;
    }

}
