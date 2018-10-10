<?php
namespace Helvetica\Standard\Library;

/**
 * A simple template tool.
 */
class Template
{
    /** @var string */
    protected $basePath = '';

    /** @var array */
    protected $values = [];

    /**
     * Render a php file by file name and assign values.
     * 
     * @param string $fileName
     * @param array $values
     * @return string
     */
    public function render($fileName, $values = [])
    {
        ob_start();
        $this->assign($values);
        extract($this->getValues());
        $this->load($fileName);

        $output = ob_get_contents();
        ob_end_clean();
        return $output;
    }

    /**
     * Require a php file by file name.
     * 
     * @param string $fileName
     */
    public function load($fileName)
    {
        $path = $this->basePath . $fileName;
        require($path);
    }

    /**
     * Merge template values
     * 
     * @param array $values
     */
    public function assign($values)
    {
        $this->values = array_merge($this->values, $values);
    }

    /**
     * Get template values
     * 
     * @return array
     */
    public function getValues()
    {
        return $this->values;
    }

    /**
     * Set base template path.
     * 
     * @param string $basePath
     */
    public function setBasePath($basePath)
    {
        $this->basePath = $basePath;
    }
}
