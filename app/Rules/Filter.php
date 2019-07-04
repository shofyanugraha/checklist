<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class Filter implements Rule
{

    // error message
    protected $error;

    // array level dimension 
    protected $level;

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        // Check Variable Array
        if(is_array($value) && count($value) > 0){
            foreach($value as $key => $val){
                $this->level = 1;
                if(is_array($val) && count($val) > 0){
                    foreach($val as $k => $v){
                        $this->level = 2;
                        if(in_array($k, ['like', '!like', 'is', '!is', 'in', '!in'])){
                            if(!is_null($v) && $v != ''){
                                // check if use like must be alphanumeric & only use  * symbol
                                if($k == 'like' || $k == '!like') {
                                    if(preg_match('/[a-zA-Z0-9*]/', $v)){
                                        return true;    
                                    } else {
                                        $this->error = 'Alphanumeric or use * use specify like';    
                                    }
                                } else {
                                    if(preg_match('/[a-zA-Z0-9]/', $v)){
                                        return true;    
                                    } else {
                                        $this->error = 'Alphanumeric';    
                                    }
                                }
                                
                            } else{
                                $this->error = 'required';
                            }
                        } else {
                            $this->error = 'in like, !like, is, !is, in, !in';
                        }
                    }
                } else {
                    $this->error = 'required';
                }

            }
        }
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        if($this->level > 0){
            return 'The :attribute array level '. $this->level .' must be '. $this->error;    
        } else {
            return 'The :attribute must be '. $this->error;    
        }
        
    }
}