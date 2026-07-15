<?php

return [

    'course_placeholder' => 'images/course-no-image.png',

    /*
    | Enabled activity keys for create UI (others appear disabled).
    */
    'enabled_activities' => ['berkas', 'video', 'url', 'penugasan', 'survey'],

    /*
    | Max upload size for activity type "berkas" (kilobytes).
    | 10240 = 10 MB
    */
    'berkas_max_kb' => (int) env('MOOC_BERKAS_MAX_KB', 10240),

    'berkas_mimes' => ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'zip', 'rar', 'txt', 'png', 'jpg', 'jpeg', 'webp'],

    /*
    | Penugasan — instruksi (admin) & hasil pengerjaan (peserta).
    | Word / PowerPoint / PDF / ZIP
    */
    'penugasan_max_kb' => (int) env('MOOC_PENUGASAN_MAX_KB', 10240),

    'penugasan_mimes' => ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'zip'],

    /*
    | Max upload size for activity type "h5p" (kilobytes).
    | 204800 = 200 MB
    */
    'h5p_max_kb' => (int) env('MOOC_H5P_MAX_KB', 204800),

    /*
    | Max upload size for activity type "video" (kilobytes).
    | 51200 = 50 MB
    */
    'video_max_kb' => (int) env('MOOC_VIDEO_MAX_KB', 51200),

    'video_mimes' => ['mp4', 'webm', 'mov', 'avi', 'mkv', 'm4v'],

];
