<?php

use Server;

class Drive {

 public static $clientId='9xx.apps.googleusercontent.com';
    public static $clientSecret='xxx';
    public static $redirectUri='[domain]/callback.php';
    
    public static function getCode($scope='https://www.googleapis.com/auth/drive',array $options=[]){

        //optional
        // if(file_exists('auth.json')){
        //     echo 'you got your auth before.you dont need do this again';
        //     return;
        // }

        $baseUrl="https://accounts.google.com/o/oauth2/v2/auth";
        $formParams=$options;
        $formParams['client_id']=self::$clientId;
        $formParams['redirect_uri']=self::$redirectUri;
        $formParams['scope']=$scope;
        $formParams['response_type']='code';
        $formParams['access_type']='offline';

        $reqUrl=$baseUrl.Server::changeArrayToGetFormat($formParams);

        return $reqUrl;
        
    }


    public static function getAccessToken($code){
        $baseUrl='https://oauth2.googleapis.com/token';
        $formParams['client_id']=self::$clientId;
        $formParams['redirect_uri']=self::$redirectUri;
        $formParams['code']=$code;
        $formParams['client_secret']=self::$clientSecret;
        $formParams['grant_type']='authorization_code';
        
        $response=json_decode(Server::sendRequest($baseUrl,$formParams,'post',['Accept'=>'application/json','Content-type'=>'application/x-www-form-urlencoded']));

        //optional
        file_put_contents('auth.json',json_encode(['access_token'=>$response->access_token,'refresh_token'=>$response->refresh_token,'token_life_time'=>time()+$response->expires_in]));
        
        return json_encode($response);
    }


    public static function refreshToken($refresh_token){
        $baseUrl='https://oauth2.googleapis.com/token';
        $formParams['client_id']=self::$clientId;
        $formParams['refresh_token']=$refresh_token;
        $formParams['client_secret']=self::$clientSecret;
        $formParams['grant_type']='refresh_token';
        $response=Server::sendRequest($baseUrl,$formParams,'post',['Accept'=>'application/json','Content-type'=>'application/x-www-form-urlencoded']);
        
        return $response;
    }


    public static function simpleUpload($file='file url or path'){

        //api base url
        $baseUrl="https://www.googleapis.com/upload/drive/v3/files?uploadType=media";

        $headers['Authorization']=self::generateAuth();

        //get file mime type
        $urlInfo=get_headers($file,1);
        $headers['Content-Type']=$urlInfo['Content-Type'];


        //get file size
        $headers['Content-Length']=$urlInfo['Content-Length'];

        $response=Server::sendFileInBody($baseUrl,$file,$headers);

        return $response;
    
    }

    public static function loadListTeamdriveId(){
        
        // load page 1, sau đó lấy pageToken của page 1, rồi gọi hàm này với pageToken của page 1
        // $page_1 = Drive::loadListTeamdrive();
        // $page_2 = Drive::loadListTeamdrive(json_decode($page_1)->nextPageToken);
        // $page_3 = Drive::loadListTeamdrive(json_decode($page_2)->nextPageToken);
        // return $page_3;

        $currentPage = Drive::loadListTeamdrive();
        // while current page have nextPageToken
        while (json_decode($currentPage)->nextPageToken) {
            // print_r($currentPage);
            // $array_drives[] = json_decode(Drive::loadListTeamdrive(json_decode($currentPage)->nextPageToken))->drives;
            $array_drives[] = json_decode($currentPage)->drives;
            $currentPage = Drive::loadListTeamdrive(json_decode($currentPage)->nextPageToken);
        }
        // $nextPage = Drive::loadListTeamdrive(json_decode($currentPage)->nextPageToken);
        header('Content-type: application/json');


        $array_drives_flat = array_merge(...$array_drives);
        file_put_contents('list_teamdrive.json', json_encode($array_drives_flat,JSON_UNESCAPED_UNICODE));

        // print_r(($array_drives_flat)) ;
        
        return json_encode($array_drives_flat);
    
    }


    public static function loadListTeamdrive($pageToken=null){

        //api base url

        $baseUrl="https://www.googleapis.com/drive/v3/drives";
        $headers['Authorization']=self::generateAuth();
        $headers['Content-type']='application/json';
        $formParams['pageSize']=100;
        $formParams['useDomainAdminAccess']=true;
        $formParams['pageToken']=$pageToken;

        $response=Server::sendRequest($baseUrl,$formParams,'get',$headers);
        header('Content-type: application/json');
        return $response;
    
    }

    // create teamdrive
    public static function createTeamdrive($name,$themeId=null){
        $baseUrl="https://www.googleapis.com/drive/v3/drives?requestId=d744b4b9-dcce-493f-b2f6-5305c5aaa093";
        $headers['Authorization']=self::generateAuth();
        $headers['Content-type']='application/json';
        $headers['Accept']='application/json';
        $formParams['name']=$name;
        // $formParams['requestId']='d744b4b9-dcce-493f-b2f6-5305c5aaa090';
        // $formParams['themeId']=$themeId;
        // $body['name']=$name;

        // $response=Server::curl($baseUrl,json_encode($formParams),$headers);

        // $response=Server::sendRequest($baseUrl,$formParams,'post',$headers);

        // $response=json_decode(Server::curl($baseUrl,'{
        //     "name": "02"
        // }',$headers,'post',true));

        // header('Content-type: application/json');
        // print_r($headers);

        // $curl = curl_init();

        // curl_setopt_array($curl, array(
        // CURLOPT_URL => 'https://www.googleapis.com/drive/v3/drives?requestId=d744b4b9-dcce-493f-b2f6-5305c5aaa091',
        // CURLOPT_RETURNTRANSFER => true,
        // CURLOPT_ENCODING => '',
        // CURLOPT_MAXREDIRS => 10,
        // CURLOPT_TIMEOUT => 0,
        // CURLOPT_FOLLOWLOCATION => true,
        // CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        // CURLOPT_CUSTOMREQUEST => 'POST',
        // CURLOPT_POSTFIELDS =>'{
        //     "name": "000002"
        // }',
        // // CURLOPT_HTTPHEADER => array(
        // //     'Content-Type: application/json',
        // //     'Accept: application/json',
        // //     'Authorization: Bearer ya29.a0AeTM1idhmz5GoBbdHnHDla39NR3uqaC2DWfsvlMhWyI-xgJBrKXUS1SEa2lqrqPatO8u4JroEWPFs4Z_YIVMJgFbuvYbdZfEBC96BiJBkUJ1zWXsegWaAnQvOsov8mXWqdXaSB5W2ftCAnC0rbGJNbx2WOFRaCgYKAf4SARESFQHWtWOms0OR-pZz4BQoh3wfTadbfQ0163'
        // // ),
        // CURLOPT_HTTPHEADER => $headers,

        // ));


        // $response = curl_exec($curl);

        // curl_close($curl);
        // echo $response;


        $response=Server::curl($baseUrl,json_encode($formParams),$headers);

        return $response;
    }


    
    
    public static function downloadFile($fileId){
        $baseUrl="https://www.googleapis.com/drive/v2/files/$fileId";

        $headers['Authorization']=self::generateAuth();

        $response=Server::sendRequest($baseUrl,[],'get',$headers);
        return $response;

    }

    public static function getFileDownloadLink($fileId){
        return "https://drive.google.com/uc?id=$fileId&export=download";
    }

    public static function makePermissianFile($role="owner or reader or writer ",$type="user or anyone",$fileId){
        //refrence https://developers.google.com/drive/api/v3/reference/permissions/create
        $baseUrl="https://www.googleapis.com/drive/v3/files/$fileId/permissions";
        $formParams['role']=$role;
        $formParams['type']=$type;
        $headers['Authorization']=self::generateAuth();
        $headers['Content-type']='application/json';

        $response=Server::curl($baseUrl,json_encode($formParams),$headers);
        echo $response;

    }
    public static function addEmailSharedDrive($fileId){
        //refrence https://developers.google.com/drive/api/v3/reference/permissions/create
        $baseUrl="https://www.googleapis.com/drive/v3/files/$fileId/permissions?supportsTeamDrives=true";
        // $formParams['supportsTeamDrives']=true;
        // $formParams['name']= 'new name'; // not working
        $formParams['role']= 'fileOrganizer';
        $formParams['type']= 'user';
        $formParams['emailAddress']= 'vulieumang@gmail.com';
        $formParams['kind']= 'drive#permission';

        
        $headers['Authorization']=self::generateAuth();
        $headers['Content-type']='application/json';

        $response=Server::curl($baseUrl,json_encode($formParams),$headers);
        echo $response;

    }

    // add email to list shared drive
    public static function addEmailSharedDriveList($arrFileId){
        foreach($arrFileId as $fileId){
            self::addEmailSharedDrive($fileId);
        }
        
    }
    


    public static function generateAuth(){

        //get auth data
        $auth=json_decode(file_get_contents('auth.json'));
        $refresh_token=$auth->refresh_token;
        $token_life_time=$auth->token_life_time;
        $access_token=$auth->access_token;

        //check token expired or not
        if($token_life_time<time()){
            //refresh token
            $response=json_decode(Drive::refreshToken($refresh_token));
            if(empty($response->error)){
                return "Bearer ".$response->access_token;
            }
            return false;
            
        }

        //return access token
        return "Bearer ".$access_token;

    }

    public static function resumableUpload($file,$title='app.jpg'){
        $baseUrl1='https://www.googleapis.com/upload/drive/v3/files?uploadType=resumable';

        $headers['Authorization']=self::generateAuth();
        
        $headers['Content-Type']="application/json; charset=UTF-8";

        $body['name']=$title;

        $response=json_decode(Server::curl($baseUrl1,json_encode($body),$headers,'post',true));
        

        if(empty($response->location)){
            return false;
        }

        //get file mime type
        $urlInfo=get_headers($file,1);
        $headers2['Content-Length']=$urlInfo['Content-Length'];
        $response=Server::curl($response->location,file_get_contents($file),$headers2,'PUT');
        
        return $response;
        
        
    }

    public static function getAboutMe($accessToken){
        $baseUrl="https://www.googleapis.com/drive/v2/about";
        $headers['Authorization']="Bearer $accessToken";
        $response=Server::sendRequest($baseUrl,[],'get',$headers);
        
        return $response;
    }

    public static function revoke($accessToken){
        $baseUrl="https://oauth2.googleapis.com/revoke";
        $formParams['token']=$accessToken;
        $headers['Content-type']="application/x-www-form-urlencoded";
        $response=Server::sendRequest($baseUrl,$formParams,'post',$headers);
        
        return $response;
    }
}
