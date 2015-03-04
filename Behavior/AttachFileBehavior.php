<?php

namespace ItBlaster\AttachFileBundle\Behavior;

/**
 * Загрузка файлов
 *
 * Class AttachFileBehavior
 * @package ItBlaster\AttachFileBundle\Behavior
 */
class AttachFileBehavior extends \Behavior
{
    protected $parameters = array(
        'file_columns'  =>  '',
        'i18n'  => ''
    );
    protected $builder;

    protected $file_columns = array();
    protected $i18n_file_columns = array();

    /**
     * Проверяем существование столбцов original_name, file_name, ext, size
     *
     * @throws InvalidArgumentException
     */
    public function modifyTable()
    {
        $table = $this->getTable();
        $file_columns = explode(',',$this->getParameter('file_columns'));
        foreach ($file_columns as $file_column) {
            $file_column = trim($file_column);
            if ($file_column) {
                $this->file_columns[]= $file_column;
            }
        }

        if ($this->getParameter('i18n')) {
            $file_columns = explode(',',$this->getParameter('i18n'));
            foreach ($file_columns as $file_column) {
                $file_column = trim($file_column);
                if ($file_column) {
                    $this->i18n_file_columns[]= $file_column;
                }
            }
        }
    }

    /**
     * Добавляем поле $file в модель
     *
     * @return string The code to be added to model class
     */
    public function objectAttributes()
    {

        $table_name = $this->getTable()->getName();
        $attributes = '
protected $class_alias = "' . $table_name . '"; //название класса в венгерском стиле
protected $files = array();
protected $file_objects = array();';

        if (count($this->file_columns)) {
            $attributes .= '
protected $file_fields = array(';
            foreach ($this->file_columns as $file_column) {
                $attributes .= '"' . $file_column . '",';
            }

            $attributes .= ');
';
        }
        return $attributes;
    }

    /**
     * добавляем методы в модель
     *
     * @param $builder
     * @return string
     */
    public function objectMethods($builder)
    {
        $this->builder = $builder;
        $script = '';

        $this->getClassAlias($script);

        if ($this->getParameter('i18n')) {
            $this->addDeleteI18nFiles($script);
        }

        if (count($this->file_columns)) {
            $this->saveFiles($script);
            $this->deleteFiles($script);
            $this->getFileObject($script);
            foreach ($this->file_columns as $file_column) {
                $this->addGetColumnFile($script, $file_column);
                $this->addSetColumnFile($script, $file_column);
                $this->addGetColumnPath($script, $file_column);
            }
        }

        if (count($this->i18n_file_columns)) {
            foreach ($this->i18n_file_columns as $file_column) {
                $this->addGetI18nColumnPath($script, $file_column);
                $this->addGetI18nColumnObject($script, $file_column);
            }
        }

        return $script;
    }

    /**
     * Удаление прикреплённых файлов к объектам i18n
     */
    public function addDeleteI18nFiles(&$script)
    {
        $script .= '
/**
 * Удаление прикреплённых файлов
 */
public function deleteI18nFiles()
{
    $files = BaseAttachFileQuery::create()
            ->filterByModel($this->getClassAlias()."_i18n")
            ->filterByObjectId($this->getId())
            ->find();
    if (count($files)) {
        $files_dir = "";
        foreach($files as $file_object) {
            $files_dir = $file_object->fullFilePathDir();
            $file_object->deleteFile();
            $file_object->delete();
        }
        $files = glob($files_dir."*.*");
        if (is_dir($files_dir) && !count($files)) { //если в папке есть ещё чьи то файлы, то папку не трогаем. Если пустая, то удаляем
            return rmdir($files_dir);
        }
        return true;
    }
}
        ';
    }

    /**
     * Алиас класса
     *
     * @param $script
     */
    protected function getClassAlias(&$script)
    {
        $script .= '
/**
 * Алиас класса
 *
 * @return string
 */
public function getClassAlias()
{
    return $this->class_alias;
}
    ';
    }

    /**
     * Метод сохранения файла в postSave
     * После сохранения объекта сохраняем загруженный файл
     *
     * @param $builder
     * @return string
     */
    public function postSave($builder)
    {
        $this->builder = $builder;
        $script = '';
        if (count($this->file_columns)) {
            $script .= "\$this->saveFiles(); //После сохранения объекта сохраняем загруженный файл";
        }
        return $script;
    }

    /**
     * Удаляем файлы перед удалением объекта
     *
     * @param $builder
     * @return string
     */
    public function preDelete($builder)
    {
        $this->builder = $builder;
        $script = '';
        if (count($this->file_columns)) {
            $script .= "
\$this->deleteFiles(); //Перед удалением объекта удаляем загруженные файлы";
        }
        if ($this->getParameter('i18n')) {
            $script .= "
\$this->deleteI18nFiles(); //Перед удалением объекта удаляем загруженные i18n файлы";
        }

        return $script;
    }

    /**
     * Перевод из венгерского стиля в CamelCase
     *
     * @param $name
     * @return mixed
     */
    protected function CamelCase($name)
    {
        return ucfirst(\Propel\PropelBundle\Util\PropelInflector::camelize($name));
    }

    /**
     * Сохраняет файлы
     *
     * @param $script
     */
    protected function  saveFiles(&$script)
    {
        $script .= '
/**
 * Сохраняем файл в uploads
 *
 * @return \Symfony\Component\HttpFoundation\File\UploadedFile
 */
public function saveFiles()
{
    if (count ($this->files)) {
        /** @var \Symfony\Component\HttpFoundation\File\UploadedFile $file */
        $need_save = 0;
        foreach ($this->files as $field => $file) {
            if ($file) {
                $file_object = $this->getFileObject($field);
                if ($file_object->getFileName() ) { //если уже какой то файл сохранён
                    $file_object->deleteFile();
                }
                $file_object->setObjectId($this->getId());
                $file_name = uniqid();
                $original_name = $file->getClientOriginalName();
                $ext = $file->getClientOriginalExtension();
                $size = $file->getSize();

                $file->move($file_object->fullFilePathDir(), $file_name . "." . $ext); //перемещаем файл в uploads
                $file_object
                    ->setOriginalName($original_name)
                    ->setFileName($file_name)
                    ->setExt($ext)
                    ->setSize($size)
                    ->save();
                $need_save++;
                $this->files[$field] = null;
                $name = ucfirst(\Propel\PropelBundle\Util\PropelInflector::camelize($field));
                $this->setByName($name, $file_object->getId());
            }
        }
        if ($need_save) {
            $this->save();
        }
    }
}
    ';
    }

    /**
     * Удаляем файлы
     *
     * @param $script
     */
    protected function deleteFiles(&$script)
    {
        $script .= '
/**
 * Удаление прикреплённых файлов
 *
 * @return bool
 */
public function deleteFiles()
{
    $files_dir = "";
    foreach($this->file_fields as $field) {
        $file_object = $this->getFileObject($field);
        $files_dir = $file_object->fullFilePathDir();
        $file_object->deleteFile();
        $file_object->delete();
    }
    $files = glob($files_dir."*.*");
    if (is_dir($files_dir) && !count($files)) { //если в папке есть ещё чьи то файлы, то папку не трогаем. Если пустая, то удаляем
        return rmdir($files_dir);
    }
    return true;
}
        ';
    }

    /**
     * Возврашает файл конкретного поля
     *
     * @param $script
     */
    protected function addGetColumnFile(&$script, $file_column)
    {
        $name = $this->CamelCase($file_column);
        $script .= '
/**
 * Возврашает файл '.$file_column.'
 *
 * @return \Symfony\Component\HttpFoundation\File\UploadedFile
 */
public function get'.$name.'File()
{
    return isset($this->files["'.$file_column.'"]) ? $this->files["'.$file_column.'"] : false;
}
    ';
    }

    /**
     * Запоминаем файл
     *
     * @param $script
     */
    protected function addSetColumnFile(&$script, $file_column)
    {
        $name = $this->CamelCase($file_column);
        $script .= '
/**
 * Устанавливает файл
 *
 * @param \Symfony\Component\HttpFoundation\File\UploadedFile $v
 */
public function set'.$name.'File($v)
{
        $this->files["'.$file_column.'"] = $v;
        if ($v) {
            $file_object = $this->getFileObject("'.$file_column.'");
            $this->set'.$name.'(uniqid());
        }
}
    ';
    }

    /**
     * Путь до файла
     *
     * @param $script
     * @param $file_column
     */
    protected function addGetColumnPath(&$script, $file_column)
    {
        $name = $this->CamelCase($file_column);
        $script .= '
/**
 * Путь до файла '.$file_column.'
 *
 * @return \Symfony\Component\HttpFoundation\File\UploadedFile
 */
public function get'.$name.'Path()
{
    $file_object = $this->getFileObject("'.$file_column.'");
    return $file_object && $file_object->issetFile() ? $file_object->getFilePath() : "";
}
    ';
    }

    /**
     * Путь до файла
     *
     * @param $script
     * @param $file_column
     */
    protected function addGetI18nColumnPath(&$script, $file_column)
    {
        $name = $this->CamelCase($file_column);
        $script .= '
/**
 * Путь до файла '.$file_column.'
 *
 * @return \Symfony\Component\HttpFoundation\File\UploadedFile
 */
public function get'.$name.'Path()
{
    return $this->getCurrentTranslation()->get'.$name.'Path();
}
    ';
    }

    /**
     * Объект файла
     *
     * @param $script
     * @param $file_column
     */
    protected function addGetI18nColumnObject(&$script, $file_column)
    {
        $name = $this->CamelCase($file_column);
        $script .= '
/**
 * Путь до файла '.$file_column.'
 *
 * @return \Symfony\Component\HttpFoundation\File\UploadedFile
 */
public function get'.$name.'Object()
{
    return $this->getCurrentTranslation()->getFileObject("'.$file_column.'");;
}
    ';
    }

    /**
     * Объект файла
     *
     * @param $script
     */
    protected function getFileObject(&$script)
    {
        $script .= '
/**
 * Объект файла
 *
 * @param $field
 * @return AttachFile
 */
public function getFileObject($field)
{
    if (!isset($this->file_objects[$field])) {
        $name = ucfirst(\Propel\PropelBundle\Util\PropelInflector::camelize($field));
        $file_object_id = $this->getByName($name);
        $file_object = $file_object_id ? BaseAttachFileQuery::create()->findOneById($file_object_id) : false;

        if ($file_object) {
            $this->file_objects[$field] = $file_object;
        } else {
            $file_object = new \ItBlaster\AttachFileBundle\Model\AttachFile();
            $file_object
                ->setObjectId($this->getId())
                ->setModel($this->getClassAlias())
                ->setField($field)
                ->setObjectId($this->getId());
            $this->file_objects[$field] = $file_object;
        }
    }
    return $this->file_objects[$field];
}
    ';
    }
}