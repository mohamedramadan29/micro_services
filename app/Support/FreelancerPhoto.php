<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Http\UploadedFile;

/**
 * A freelancer's professional photo: uploaded by the freelancer (profile pages) or replaced by the
 * admin while reviewing; stored as centred squares at 128/256/512 px (ImageVariants).
 */
class FreelancerPhoto
{
    public const DIR = 'assets/uploads/freelancers';

    /** Validation for an upload (min 400×400 so it stays sharp). */
    public const RULE = 'image|mimes:jpg,jpeg,png,webp|max:5120|dimensions:min_width=400,min_height=400';

    public const MESSAGES = [
        'image' => 'اختر ملف صورة',
        'mimes' => 'الصورة يجب أن تكون jpg أو png أو webp',
        'max' => 'حجم الصورة يجب ألا يتجاوز 5 ميجابايت',
        'dimensions' => 'الصورة صغيرة: أقل مقاس 400×400 بكسل حتى تظهر واضحة',
    ];

    /** Validation messages for a field name. */
    public static function messages(string $field): array
    {
        $out = [];
        foreach (self::MESSAGES as $rule => $text) {
            $out[$field . '.' . $rule] = $text;
        }

        return $out;
    }

    /**
     * A freelancer who uploaded his account picture before the professional photo existed
     * (assets/uploads/users_image) gets it as his professional photo when it is sharp enough.
     */
    public static function promoteProfileImage(User $user): bool
    {
        if ($user->professional_photo || ! $user->image) {
            return false;
        }
        $path = public_path('assets/uploads/users_image/' . basename((string) $user->image));
        $size = is_file($path) ? @getimagesize($path) : false;
        if (! $size || $size[0] < 400 || $size[1] < 400) {
            return false;
        }
        try {
            self::replace($user, new UploadedFile($path, basename($path), null, null, true));
        } catch (\Throwable $e) {
            report($e);

            return false;
        }

        return true;
    }

    public static function replace(User $user, UploadedFile $file): void
    {
        $base = ImageVariants::storeSquare($file, self::DIR);
        ImageVariants::delete(self::DIR, $user->professional_photo);
        $user->forceFill(['professional_photo' => $base])->save();
    }
}
