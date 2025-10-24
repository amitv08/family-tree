<?php
/**
 * Member Validator - Validates member data
 *
 * @package FamilyTree
 * @since 2.4.0
 */

namespace FamilyTree\Validators;

if (!defined('ABSPATH')) exit;

class MemberValidator {
    /**
     * Validate member data
     *
     * @param array $data Member data
     * @param int|null $member_id Member ID (for updates)
     * @return array Array of error messages
     */
    public static function validate(array $data, ?int $member_id = null): array {
        $errors = [];

        // Field length validation
        if (isset($data['first_name']) && strlen($data['first_name']) > 100) {
            $errors[] = 'First name is too long (maximum 100 characters).';
        }

        if (isset($data['last_name']) && strlen($data['last_name']) > 100) {
            $errors[] = 'Last name is too long (maximum 100 characters).';
        }

        if (isset($data['gender']) && strlen($data['gender']) > 20) {
            $errors[] = 'Gender value is too long (maximum 20 characters).';
        }

        if (isset($data['photo_url']) && strlen($data['photo_url']) > 255) {
            $errors[] = 'Photo URL is too long (maximum 255 characters).';
        }

        // Photo URL validation
        if (!empty($data['photo_url'])) {
            $errors = array_merge($errors, self::validate_photo_url($data['photo_url']));
        }

        if (isset($data['biography']) && strlen($data['biography']) > 10000) {
            $errors[] = 'Biography is too long (maximum 10,000 characters).';
        }

        if (isset($data['address']) && strlen($data['address']) > 500) {
            $errors[] = 'Address is too long (maximum 500 characters).';
        }

        if (isset($data['city']) && strlen($data['city']) > 100) {
            $errors[] = 'City is too long (maximum 100 characters).';
        }

        if (isset($data['state']) && strlen($data['state']) > 100) {
            $errors[] = 'State is too long (maximum 100 characters).';
        }

        if (isset($data['country']) && strlen($data['country']) > 100) {
            $errors[] = 'Country is too long (maximum 100 characters).';
        }

        if (isset($data['postal_code']) && strlen($data['postal_code']) > 20) {
            $errors[] = 'Postal code is too long (maximum 20 characters).';
        }

        // Parent validation
        $errors = array_merge($errors, self::validate_parents($data, $member_id));

        // Date validation
        $errors = array_merge($errors, self::validate_dates($data));

        return $errors;
    }

    /**
     * Validate photo URL
     *
     * @param string $url Photo URL
     * @return array Error messages
     */
    private static function validate_photo_url(string $url): array {
        $errors = [];
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'];
        $parsed_url = parse_url($url);

        if ($parsed_url && isset($parsed_url['path'])) {
            $path_info = pathinfo($parsed_url['path']);
            $extension = isset($path_info['extension']) ? strtolower($path_info['extension']) : '';

            if (!empty($extension) && !in_array($extension, $allowed_extensions)) {
                $errors[] = 'Photo URL must be a valid image file (jpg, jpeg, png, gif, webp, bmp, or svg).';
            }
        }

        return $errors;
    }

    /**
     * Validate parent relationships
     *
     * @param array $data Member data
     * @param int|null $member_id Member ID
     * @return array Error messages
     */
    private static function validate_parents(array $data, ?int $member_id): array {
        $errors = [];
        $parent1_id = !empty($data['parent1_id']) ? intval($data['parent1_id']) : null;
        $parent2_id = !empty($data['parent2_id']) ? intval($data['parent2_id']) : null;

        // Person cannot be their own parent
        if ($member_id && ($parent1_id == $member_id || $parent2_id == $member_id)) {
            $errors[] = 'A person cannot be their own parent.';
        }

        // Parents must be different
        if ($parent1_id && $parent2_id && $parent1_id == $parent2_id) {
            $errors[] = 'Parent 1 and Parent 2 must be different people.';
        }

        return $errors;
    }

    /**
     * Validate dates
     *
     * @param array $data Member data
     * @return array Error messages
     */
    private static function validate_dates(array $data): array {
        $errors = [];

        // Birth vs Death date
        if (!empty($data['birth_date']) && !empty($data['death_date'])) {
            $birth = strtotime($data['birth_date']);
            $death = strtotime($data['death_date']);

            if ($death < $birth) {
                $errors[] = 'Death date cannot be before birth date.';
            }

            $age = ($death - $birth) / (365.25 * 24 * 60 * 60);
            if ($age > 150) {
                $errors[] = 'Age seems unrealistic (over 150 years). Please verify dates.';
            }
        }

        // Birth year validation
        if (!empty($data['birth_date'])) {
            $birth_year = (int)date('Y', strtotime($data['birth_date']));
            $current_year = date('Y');

            if ($birth_year < 1800) {
                $errors[] = 'Birth year seems too old. Please verify.';
            }

            if ($birth_year > $current_year) {
                $errors[] = 'Birth year cannot be in the future.';
            }
        }

        return $errors;
    }
}
