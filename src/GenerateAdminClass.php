<?php

namespace PhangoApp\PhaLibs;

use PhangoApp\PhaLibs\SimpleList;
use PhangoApp\PhaView\View;
use PhangoApp\PhaModels\Webmodel;
use PhangoApp\PhaModels\ModelForm;
use PhangoApp\PhaUtils\Utils;
use PhangoApp\PhaRouter\Routes;
use PhangoApp\PhaI18n\I18n;

class GenerateAdminClass {

    public $model_name='';

    public $list;
    
    //For the future
    
    public $arr_fields_insert=array();
    
    public $arr_fields_edit=array();
    
    public $yes_search=1;
    
    public $enctype='';
    
    public $url='';
    
    public $safe=0;
    
    public function __construct($model_name, $url)
    {
    
        $this->model_name=$model_name;
        
        $this->list=new SimpleList($model_name);
        
        $this->set_url_admin($url);
        
        Webmodel::$model[$this->model_name]->create_forms($this->arr_fields_edit);
    
    }
    
    public function show()
    {
        
        settype($_GET['op_admin'], 'integer');
        
        switch($_GET['op_admin'])
        {
            
            //List
            
            default:
                
                //$this->list->show();
                echo View::load_view(array($this), 'admin/adminlist');
            
            break;
    
            //Create new item
            
            case 1:
            
                $action=Routes::add_get_parameters($this->url, array('op_admin' => 2));
            
                $this->form(array(), Routes::add_get_parameters($this->url, array('op_admin' => 2)));
            
            break;
            
            case 2:
            
                if(!Webmodel::$model[$this->model_name]->insert($_POST, $this->safe))
                {
                    
                    $this->form($_POST, Routes::add_get_parameters($this->url, array('op_admin' => 2)), 1);
                
                }
                else
                {
                
                    View::set_flash(I18n::lang('common', 'item_insert', 'Item inserted succesfully'));
                    
                    Routes::redirect($this->url);
                
                }
               
            
            break;
            
            case 3:
            
                settype($_GET[Webmodel::$model[$this->model_name]->idmodel], 'integer');
                
                $id=$_GET[Webmodel::$model[$this->model_name]->idmodel];
                
                $idmodel=Webmodel::$model[$this->model_name]->idmodel;
                
                $arr_row=Webmodel::$model[$this->model_name]->select_a_row($id);
                
                settype($arr_row[$idmodel], 'integer');
                
                if($arr_row[$idmodel]>0)
                {
                
                    if(Routes::$request_method=='GET')
                    {
                
                        $this->form($arr_row, Routes::add_get_parameters($this->url, array('op_admin' => 3, $idmodel => $id)), 1);
                        
                    }
                    else
                    if(Routes::$request_method=='POST')
                    {
                        Webmodel::$model[$this->model_name]->set_conditions('WHERE '.$idmodel.'='.$id);
                    
                        if(!Webmodel::$model[$this->model_name]->update($_POST, $this->safe))
                        {
                        
                            $this->form($arr_row, Routes::add_get_parameters($this->url, array('op_admin' => 3, $idmodel => $id)), 1);
                        
                        }
                        else
                        {
                        
                            View::set_flash(I18n::lang('common', 'item_updated', 'Item update succesfully'));
                    
                            Routes::redirect($this->url);
                        
                        }
                    
                    }
                    
                }
            
            break;
            
            case 4:
                
                settype($_GET[Webmodel::$model[$this->model_name]->idmodel], 'integer');
                
                $id=$_GET[Webmodel::$model[$this->model_name]->idmodel];
                
                $idmodel=Webmodel::$model[$this->model_name]->idmodel;
                
                Webmodel::$model[$this->model_name]->set_conditions('WHERE '.$idmodel.'='.$id);
                
                if(Webmodel::$model[$this->model_name]->delete($_POST, $this->safe))
                {
                
                    View::set_flash(I18n::lang('common', 'item_deleted', 'Item deleted succesfully'));
                    
                    Routes::redirect($this->url);
                    
                }
                else
                {
                
                    echo '<p>'.I18n::lang('common', 'item_deleted_error', 'Error, cannot delete the field. Please, check for errors').'</p>';
                
                }
                
            break;
        
        }
    
    }
    
    public function form($post, $action, $show_error=0)
    {
    
        ModelForm::pass_errors_to_form(Webmodel::$model[$this->model_name]);
    
        ModelForm::set_values_form($post, Webmodel::$model[$this->model_name]->forms, $show_error);
        
        $fields=$this->arr_fields_edit;
        
        $method='post';
        
        
        echo View::load_view(array(Webmodel::$model[$this->model_name]->forms, $fields, $method, $action, $this->enctype), 'forms/updatemodelform');
    
    }
    
    public function set_url_admin($url)
    {
    
        $this->list->url_options=$url;
        $this->url=$url;
    
    }

}

?>