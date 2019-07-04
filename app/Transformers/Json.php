<?php
namespace App\Transformers;


/**
*  Class Json is transformers from raw data to json view
*/
class Json
{
	public static function response($data = null, $message = null, $code = 200, $additional=null)
    {	
        if ($message==null) {
            $message = __('message.success');
        }
        if ($data==null) {
            $data = [];
        }
        $result = [];
    	
        if ($data instanceof \Illuminate\Pagination\LengthAwarePaginator) {
            $result['meta']['count'] = $data->offset();
            $result['meta']['total'] = $data->total();
            $result['links']['first']=$data->firstPage();
            $result['links']['last']=$data->lastPage();
            $result['links']['next']=$data->nextPageUrl();
            $result['links']['prev']=$data->previousPageUrl();
    		$result['data'] = $data->all();
            $result['data']['links']['self'] = $data->currentpage();;
    	} else {
    		$result['data'] = $data;
    	}

        if ($additional!=null) {
            foreach ($additional as $add) {
                $result['meta'][$add['name']] = $add['data'];
            }
        }

	    return response()->json($result, $code);
    }

    public static function exception($message = null, $error = null, $code=401)
    {	
        if ($message==null) {
            $message = __('message.error');
        }

	    $result['message'] = $message;
	    $result['status'] = false;
        if ($error instanceof \ErrorException) {    
            $result['error']['message'] = $error->getMessage();
            $result['error']['file'] = $error->getFile();
            $result['error']['line'] = $error->getLine();
        } elseif(is_array($error) && count($error) > 0) {
    	   $result['error'] = $error; 
        }
	    return response()->json($result, 200);
    }
    
}

