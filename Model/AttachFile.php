<?php

namespace ItBlaster\AttachFileBundle\Model;

use ItBlaster\AttachFileBundle\Model\om\BaseAttachFile;

class AttachFile extends BaseAttachFile
{
    /**
     * Удаление файла
     *
     * @return bool
     */
    public function deleteFile()
    {
        $file_path = $this->fullFilePath();
        if (file_exists($file_path)) {
            unlink($file_path);
        }
        $this
            ->setOriginalName("")
            ->setFileName("")
            ->setExt("")
            ->setSize("")
            ->save();
        return true;
    }

    /**
     * Полный путь до папки файла на сервере
     *
     * @return string
     */
    public function fullFilePathDir()
    {
        return $this->getProjectPath().$this->getFilePathDir();
    }

    /**
     * Полный путь файла на сервере
     *
     * @return string
     */
    public function fullFilePath()
    {
        return $this->getProjectPath().$this->getFilePath();
    }

    /**
     * Путь до файла
     *
     * /uploads/productmateriali18n/54956796bdc5c.pdf
     *
     * @return string
     */
    public function getFilePath()
    {
        return $this->getFilePathDir().$this->getFileName().".".$this->getExt();
    }

    /**
     * Папка, где лежит файл
     *
     * @return string
     */
    protected function getFilePathDir()
    {
        return "/uploads/".$this->getModel()."/".$this->getObjectId()."/";
    }

    /**
     * Папка проекта на сервере
     *
     * TODO: написать специальный бихейвор для класса AttachFile,
     * который будет в BaseAttachFile прописывать путь project_path
     * на основе AppKernel
     *
     * @return mixed
     */
    public function getProjectPath(){
        return $_SERVER["DOCUMENT_ROOT"];
    }

    /**
     * Существует ли файл физически на сервере
     *
     * @return bool
     */
    public function issetFile()
    {
        return $this->getFileName() && file_exists($this->fullFilePath());
    }

    /**
     * Является ли файл изображением
     *
     * @return bool
     */
    public function isImage()
    {
        //$_SERVER["DOCUMENT_ROOT"] = '/home/user_name/projects/project_name/web';
        if ($this->issetFile()) {
            $mime_type = mime_content_type($this->fullFilePath());
            if ($mime_type) {
                $mime_type_params = explode('/', $mime_type);
                return $mime_type_params[0] == 'image';
            }
        }
        return false;
    }
}
