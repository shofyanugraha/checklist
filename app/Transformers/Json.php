<?php
namespace App\Transformers;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
            $dt = $data->toArray();
            // dd($dt);
            $result['meta']['count'] = $dt['to'];
            $result['meta']['total'] = $dt['total'];
            $result['links']['first']=$dt['first_page_url'];
            $result['links']['last']=$dt['last_page_url'];
            $result['links']['next']=$dt['next_page_url'];
            $result['links']['prev']=$dt['prev_page_url'];
    		$result['data'] = $dt['data'];
            $result['data']['links']['self'] = app('url')->full() ;
    	} else {
    		$result['data'] = $data;
            if(is_array($data) && count($data) > 1){
                $result['meta']['count'] = count($data);
                $result['meta']['total'] = count($data);
            } else {
                $result['data']['links'] = ['self'=>app('url')->full()] ;
            }
    	}

        if ($additional!=null) {
            $result['included'] = $additional;
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
        // dd();
        if ($error instanceof NotFoundHttpException) {    
            $result['error']['message'] = $error->getMessage();
            $result['error']['file'] = $error->getFile();
            $result['error']['line'] = $error->getLine();
        } elseif ($error instanceof \Exception) {    

            $result['error']['message'] = $error->getMessage();
            $result['error']['file'] = $error->getFile();
            $result['error']['line'] = $error->getLine();
        } elseif(is_array($error) && count($error) > 0) {
    	   $result['error'] = $error; 
        }
	    return response()->json($result, $code);
    }
    
}

