<?php

namespace controllers;

use Acheteteper\ControllerBase;
use Acheteteper\FileUpload;

class UploadDemoController extends ControllerBase
{
    public function index()
    {
        return $this->render('upload_demo');
    }

    public function submit()
    {
        $this->requirePost();
        $this->requireCsrf();

        $data = [
            'errors' => [],
            'success' => false,
        ];

        if (!FileUpload::has('file')) {
            $data['errors'][] = 'No file uploaded';
        } else {
            if (!FileUpload::validateType('file', ['jpg', 'png', 'gif', 'webp'])) {
                $data['errors'][] = 'File must be an image (jpg, png, gif, webp)';
            }

            if (!FileUpload::validateSize('file', 2 * 1024 * 1024)) {
                $data['errors'][] = 'File size must be less than 2MB';
            }

            if (empty($data['errors'])) {
                $file = FileUpload::get('file');
                $detectedExtension = FileUpload::detectedExtension('file');
                $filename = FileUpload::randomImageName('file');
                if ($filename === null) {
                    $this->fail(400, 'Invalid image');
                }
                $destination = $this->config()->getUserConfig('uploadsPath') . '/' . $filename;

                if (FileUpload::move('file', $destination)) {
                    $data['success'] = true;
                    $data['file'] = [
                        'name' => $file['name'],
                        'size' => $file['size'],
                        'type' => $file['type'],
                        'detectedExtension' => $detectedExtension,
                        'filename' => $filename,
                    ];
                } else {
                    $data['errors'][] = 'Failed to move uploaded file';
                }
            }
        }

        return $this->render('upload_demo', $data);
    }
}
