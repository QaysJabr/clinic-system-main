<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Landing demo video
    |--------------------------------------------------------------------------
    |
    | LANDING_DEMO_VIDEO_URL — رابط YouTube/Vimeo (مثال: https://www.youtube.com/watch?v=XXXX)
    | LANDING_DEMO_VIDEO_EMBED — رابط embed مباشر (يُفضّل إن وُجد)
    |
    */

    'demo_video_url' => env('LANDING_DEMO_VIDEO_URL', ''),

    'demo_video_embed' => env('LANDING_DEMO_VIDEO_EMBED', ''),

    /** Public APK download link shown on the landing page (Expo build artifact URL). */
    'android_apk_url' => env('MOBILE_ANDROID_APK_URL', ''),

];
