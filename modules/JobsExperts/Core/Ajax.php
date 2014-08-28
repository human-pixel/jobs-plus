<?php

/**
 * Author: Hoang Ngo
 */
class JobsExperts_Core_Ajax extends JobsExperts_Framework_Module
{
    const NAME = __CLASS__;

    public function __construct()
    {
        $this->_add_ajax_action('expert_like', 'handler_like');
        $this->_add_ajax_action('job_validate', 'ajax_validate');
        $this->_add_ajax_action('job_save', 'save_model');
        $this->_add_ajax_action('send_email', 'send_contact');
    }

    function send_contact()
    {
        if (wp_verify_nonce($_POST['_nonce'], 'send_email')) {
            $class = $_POST['class'];
            $model = new $class;
            $data = $_POST['data'];
            $id = $_POST['id'];
            foreach ($data as $row) {
                $name = str_replace(array(
                    $class, '[', ']'
                ), '', $row['name']);
                $value = $row['value'];
                $model->$name = $value;
            }
            if (!$model->validate()) {
                echo json_encode(array(
                    'status' => 0,
                    'errors' => $model->get_errors()
                ));
            } else {
                $type = $_POST['type'];
                if ($type == 'job') {
                    $result = JobsExperts_Core_Controllers_Job::send_contact($id, $model->export());
                    echo json_encode(array(
                        'status' => 1,
                        'url' => $result
                    ));
                } else {
                    $result = JobsExperts_Core_Controllers_Pro::send_contact($id, $model->export());
                    echo json_encode(array(
                        'status' => 1,
                        'url' => $result
                    ));
                }
            }
        }
        exit;
    }

    function save_model()
    {
        if (wp_verify_nonce($_POST['_nonce'], 'job_save')) {
            $class = $_POST['class'];
            $id = $_POST['id'];
            $page_module = JobsExperts_Plugin::instance()->page_module();
            if (class_exists($class)) {
                $model = $class::instance()->get_one($id);
                if (is_object($model)) {
                    $status = $_POST['status'];
                    $data = $_POST['data'];
                    foreach ($data as $row) {
                        $name = str_replace(array(
                            $class, '[', ']'
                        ), '', $row['name']);
                        $value = $row['value'];
                        $model->$name = $value;
                    }
                    if ($model->validate()) {
                        $model->status = $status;
                        $model->save();
                        if ($model instanceof JobsExperts_Core_Models_Job) {
                            $link = $model->status == 'publish' ? get_permalink($model->id) : get_permalink($page_module->page($page_module::MY_JOB));
                        } else {
                            $link = $model->status == 'publish' ? get_permalink($model->id) : get_permalink($page_module->page($page_module::MY_EXPERT));
                        }
                        echo json_encode(array(
                            'status' => 1,
                            'id' => $model->id,
                            'url' => $link
                        ));
                    } else {
                        echo json_encode(array(
                            'status' => 0,
                            'errors' => $model->get_errors()
                        ));
                    }
                }
            }
        }
        exit;
    }

    function ajax_validate()
    {
        if (wp_verify_nonce($_POST['_nonce'], 'job_validate')) {
            $class = $_POST['class'];
            $id = isset($_POST['id']) ? $_POST['id'] : 0;
            if (class_exists($class)) {

                if (!empty($id)) {
                    $model = $class::instance()->get_one($id);
                    if (is_object($model)) {
                        $attribute = str_replace(array(
                            $class, '[', ']'
                        ), '', $_POST['key']);

                        //binding
                        foreach ($_POST['data'] as $row) {
                            $name = str_replace(array(
                                $class, '[', ']'
                            ), '', $row['name']);
                            $value = $row['value'];
                            $model->$name = $value;
                        }
                        $model->validate();
                        $error = $model->get_error($attribute);

                        if (empty($error)) {
                            $model->addition_validate();
                            $error = $model->get_error($attribute);
                        }

                        if (!empty($error)) {
                            echo json_encode(array(
                                'status' => 0,
                                'error' => $error
                            ));
                        } else {
                            echo json_encode(array(
                                'status' => 1
                            ));
                        }
                    }
                } else {
                    $model = new $class();
                    //todo cleanup
                    $attribute = str_replace(array(
                        $class, '[', ']'
                    ), '', $_POST['key']);

                    //binding
                    foreach ($_POST['data'] as $row) {
                        $name = str_replace(array(
                            $class, '[', ']'
                        ), '', $row['name']);
                        $value = $row['value'];
                        $model->$name = $value;
                    }
                    $model->validate();
                    $error = $model->get_error($attribute);

                    if (empty($error)) {
                        $model->addition_validate();
                        $error = $model->get_error($attribute);
                    }

                    if (!empty($error)) {
                        echo json_encode(array(
                            'status' => 0,
                            'error' => $error
                        ));
                    } else {
                        echo json_encode(array(
                            'status' => 1
                        ));
                    }
                }
            }
        }
        exit;
    }

    function handler_like()
    {
        if (wp_verify_nonce($_POST['_nonce'], 'expert_like')) {
            $id = $_POST['id'];
            $model = JobsExperts_Core_Models_Pro::instance()->get_one($id);
            if (is_object($model)) {
                $user = @get_user_by('id', $_POST['user_id']);
                if ($user instanceof WP_User) {
                    if ($model->is_current_user_can_like($user->ID)) {
                        add_user_meta($user->ID, 'jbp_pro_liked', $model->id);
                        //update pro like
                        update_post_meta($model->id, 'jbp_pro_like_count', $model->get_like_count() + 1);
                        echo $model->get_like_count();
                    }
                }
            }
        }
        exit;
    }
}