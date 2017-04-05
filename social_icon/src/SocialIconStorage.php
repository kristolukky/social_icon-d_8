<?php

namespace Drupal\social_icon;

/**
 * Class SocialIconStorage.
 */
class SocialIconStorage {

    /**
     * Save an entry in the database.
     *
     * Exception handling is shown in this example. It could be simplified
     * without the try/catch blocks, but since an insert will throw an exception
     * and terminate your application if the exception is not handled, it is best
     * to employ try/catch.
     *
     * @param array $entry
     *   An array containing all the fields of the database record.
     *
     * @return int
     *   The number of updated rows.
     *
     * @throws \Exception
     *   When the database insert fails.
     *
     */
    public static function insert($entry) {
        $return_value = NULL;
        try {
            $return_value = \Drupal::database()->insert('social_icon')
                ->fields($entry)
                ->execute();
        }
        catch (\Exception $e) {
            drupal_set_message(t('db_insert failed. Message = %message, query= %query', array(
                    '%message' => $e->getMessage(),
                    '%query' => $e->getTraceAsString(),
                )
            ), 'error');
        }
        return $return_value;
    }

    /**
     * Update an entry in the database.
     *
     * @param array $entry
     *   An array containing all the fields of the item to be updated.
     *
     * @return int
     *   The number of updated rows.
     *
     * @see db_update()
     */
    public static function update($entry) {
        try {
            // \Drupal::database()->update()...->execute() returns the number of rows updated.
            $count = \Drupal::database()->update('social_icon')
                ->fields($entry)
                ->condition('id', $entry['id'])
                ->execute();
        }
        catch (\Exception $e) {
            drupal_set_message(t('db_update failed. Message = %message, query= %query', array(
                    '%message' => $e->getMessage(),
                    '%query' => $e->getTraceAsString(),
                )
            ), 'error');
        }
        return $count;
    }

    /**
     * Delete an entry from the database.
     *
     * @param array $entry
     *   An array containing at least the person identifier 'pid' element of the
     *   entry to delete.
     */
    public static function delete($entry) {
        \Drupal::database()->delete('social_icon')
            ->condition('id', $entry['id'])
            ->execute();
    }

    /**
     * @param array $entry
     *   An array containing all the fields used to search the entries in the
     *   table.
     *
     * @return object
     *   An object containing the loaded entries if found.
     *
     */
    public static function load($entry = array()) {
        // Read all fields from the social_icon table.
        $select = \Drupal::database()->select('social_icon', 'icons');
        $select->fields('icons');

        // Add each field and value as a condition to this query.
        foreach ($entry as $field => $value) {
            $select->condition($field, $value);
        }
        // Return the result in object format.
        return $select->execute()->fetchAll();
    }

}
