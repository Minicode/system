<?php
/**
 * Minicode - only need to need!
 *
 * An open source hyper-light web application agile development framework
 *
 * @package       Minicode
 * @author        Wanglong
 * @copyright     Copyright (c) 2012 - 2013, Minicode.
 * @license       http://minicode.org/docs/license
 * @link          http://minicode.org
 */

// ------------------------------------------------------------------------

/**
 * UploadFile Class
 *
 * File Uploading Class
 *
 * @package       Minicode
 * @category      Libraries
 * @subpackage    UploadFile
 * @author        Wanglong
 * @link          http://minicode.org/docs/libraries/upload_file
 */

class UploadFile {

    /**
     * Form input field name
     *
     * @access private
     * @var     string
     */
    private $field         = '';

    // --------------------------------------------------------------------

    /**
     * Upload to path
     *
     * @access  private
     * @var     string
     */
    private $upload_path   = '';

    // --------------------------------------------------------------------

    /**
     * Upload file maximum capacity
     *
     * No limit is 0, but cannot exceed the 
     * limitation of the PHP configuration
     *
     * @access  private
     * @var     int
     */
    private $max_size      = 0;

    // --------------------------------------------------------------------

    /**
     * Allow suffix names
     *
     * @access  private
     * @var     array
     */
    private $allowed_types = array('jpg', 'gif', 'png');

    // --------------------------------------------------------------------

    /**
     * Random character filename
     *
     * @access  private
     * @var     array
     */
    private $file_norepeat = TRUE;

    // --------------------------------------------------------------------

    /**
     * Custom subdirectories
     *
     * @access  private
     * @var     string
     */
    private $sub_dir       = '';

    // --------------------------------------------------------------------

    /**
     * Original file name
     *
     * @access  private
     * @var     string
     */
    private $orgn_name;

    // --------------------------------------------------------------------

    /**
     * Temp file
     *
     * @access  private
     * @var     string
     */
    private $file_temp;

    // --------------------------------------------------------------------

    /**
     * New uploaded file path
     *
     * @access  private
     * @var     string
     */
    private $file_path;

    // --------------------------------------------------------------------

    /**
     * New uploaded file name
     *
     * @access  private
     * @var     string
     */
    private $file_name;

    // --------------------------------------------------------------------

    /**
     * Upload file size
     *
     * @access  private
     * @var     string
     */
    private $file_size;

    // --------------------------------------------------------------------

    /**
     * Upload file name suffix
     *
     * @access  private
     * @var     string
     */
    private $file_ext;

    // --------------------------------------------------------------------

    /**
     * Upload error message
     *
     * @access  private
     * @var     string
     */
    private $error_msg     = '';

    // --------------------------------------------------------------------

    /**
     * Constructor
     *
     * @access  public
     * @return  void
     */
    public function __construct() {}

    // --------------------------------------------------------------------

    /**
     * Began to upload
     *
     * @access  public
     * @return  boolean
     */
    public function upload() {
        // form upload field exist ?
        if (isset($_FILES[$this->field])) {
            $this->orgn_name = $_FILES[$this->field]['name'];
            $this->file_temp = $_FILES[$this->field]['tmp_name'];
            $this->file_size = $_FILES[$this->field]['size'];
            $this->file_ext  = ltrim(strrchr($this->orgn_name, '.'), '.');
        }

        // check the upload file
        if ( ! $this->validate_file()) {
            return FALSE;
        }

        // has a sub directory ?
        // it usually used to create time folder
        // eg: /uploads/09232012/flower.jpg
        if ( ! empty($this->sub_dir)) {
            $this->upload_path .= $this->sub_dir;
            if ( ! is_dir($this->upload_path)) {
                mkdir($this->upload_path);
            }
        }

        // move the file
        if (move_uploaded_file($this->file_temp, $this->generate_file_path()) === FALSE) {
            $this->error_msg = "upload_fail";
            return FALSE;
        }

        return $this->data();
    }

    // --------------------------------------------------------------------

    /**
     * Set field name
     *
     * @access  public
     * @param   string
     * @return  void
     */
    public function set_field($field) {
        $this->field = $field;
    }

    // --------------------------------------------------------------------

    /**
     * Set Upload Path
     *
     * @access  public
     * @param   string
     * @return  void
     */
    public function set_upload_path($path) {
        // make sure it has a trailing slash
        $this->upload_path = rtrim($path, '/') . '/';
    }

    // --------------------------------------------------------------------

    /**
     * Set Maximum File Size
     *
     * @access  public
     * @param   int
     * @return  void
     */
    public function set_max_size($n) {
        $this->max_size = ((int) $n < 0) ? 0 : (int) $n;
    }

    // --------------------------------------------------------------------

    /**
     * Set Allowed File Types
     *
     * @access  public
     * @param   array
     * @return  void
     */
    public function set_allowed_types($types) {
        $this->allowed_types = $types;
    }

    // --------------------------------------------------------------------

    /**
     * Set sub directory
     *
     * @access  public
     * @param   int
     * @return  void
     */
    public function set_sub_dir($dir) {
        $this->sub_dir = trim($dir, '/') . '/';
    }

    // --------------------------------------------------------------------

    /**
     * Display the error message
     *
     * @access  public
     * @return  string
     */
    public function error() {
        return $this->error_msg;
    }

    // --------------------------------------------------------------------

    /**
     * Generates a correct upload file path
     *
     * @access  private
     * @return  string
     */
    private function generate_file_path() {
        // determine filename
        $orgn_prename = rtrim($this->orgn_name, '.' . $this->file_ext);
        $this->file_name = $this->file_norepeat
            ? $orgn_prename . '_' . time() . '_' . rand(10000, 99999) . '.' . $this->file_ext 
            : $this->orgn_name;

        $this->file_path = $this->upload_path . $this->file_name;

        return $this->file_path;
    }

    // --------------------------------------------------------------------

    /**
     * Check the upload file
     *
     * @access  private
     * @return  void
     */
    private function validate_file() {
        if (empty($this->orgn_name) || 
            empty($this->file_temp) || 
            empty($this->file_size))
        {
            $this->error_msg = 'upload_no_file_selected';
        }

        elseif (@is_dir($this->upload_path) === false) {
            $this->error_msg = 'upload_directory_does_not_exist.';
        }

        elseif (@is_writable($this->upload_path) === false) {
            $this->error_msg = 'upload_unable_to_write_file';
        }

        elseif (@is_uploaded_file($this->file_temp) === false) {
            $this->error_msg = 'upload_no_temp_directory';
        }

        elseif ($this->max_size !== 0 && $this->file_size > $this->max_size) {
            $this->error_msg = 'upload_file_exceeds_form_limit';
        }

        elseif (in_array($this->file_ext, $this->allowed_types) === false) {
            $this->error_msg = 'upload_stopped_by_extension';
        }

        else {
            return TRUE;
        }

        return FALSE;
    }

    // --------------------------------------------------------------------

    /**
     * Finalized Data Array
     *
     * Returns an associative array containing all of the information
     * related to the upload, allowing the developer easy access in one array.
     *
     * @param   string
     * @return  mixed
     */
    private function data($index = NULL) {
        $data = array(
                'orgn_name' => $this->orgn_name,
                'file_name' => $this->file_name,
                'file_path' => $this->upload_path,
                'full_path' => $this->upload_path . $this->file_name,
                'file_ext'  => $this->file_ext,
                'file_size' => $this->file_size
            );

        if ( ! empty($index)) {
            return isset($data[$index]) ? $data[$index] : NULL;
        }

        return $data;
    }
}

// END UploadFile Class
// By Minicode