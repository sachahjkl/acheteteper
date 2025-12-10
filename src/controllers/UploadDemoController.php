<?php

namespace Controllers;

use Httpeur\ControllerBase;
use Httpeur\FileUpload;

class UploadDemoController extends ControllerBase
{
    public function index()
    {
        $this->render('upload_demo');
    }

    public function submit()
    {
        $this->requirePost();

        $data = [
            'errors' => [],
            'success' => false,
        ];

        if (!FileUpload::has('file')) {
            $data['errors'][] = 'No file uploaded';
        } else {
            if (!FileUpload::validateType('file', ['jpg', 'jpeg', 'png', 'gif'])) {
                $data['errors'][] = 'File must be an image (jpg, jpeg, png, gif)';
            }

            if (!FileUpload::validateSize('file', 2 * 1024 * 1024)) {
                $data['errors'][] = 'File size must be less than 2MB';
            }

            if (empty($data['errors'])) {
                $file = FileUpload::get('file');
                $extension = FileUpload::extension('file');
                $filename = uniqid() . '.' . $extension;
                $destination = __DIR__ . '/../uploads/' . $filename;

                if (FileUpload::move('file', $destination)) {
                    $data['success'] = true;
                    $data['file'] = [
                        'name' => $file['name'],
                        'size' => $file['size'],
                        'type' => $file['type'],
                        'filename' => $filename,
                    ];
                } else {
                    $data['errors'][] = 'Failed to move uploaded file';
                }
            }
        }

        $this->render('upload_demo', $data);
    }
}
