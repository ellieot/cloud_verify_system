<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    <form action="" method="POST">
        card <input type="text" name="card">
        <input type="submit" value="提交">
    </form>

    <?php
    $key = 'resunoon';
    $iv = '3364946464643364';
    $safeword = 'imsaferightnow';

    $onlineip = '';
    if (getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
        $onlineip = getenv('HTTP_CLIENT_IP');
    } elseif (getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
        $onlineip = getenv('HTTP_X_FORWARDED_FOR');
    } elseif (getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
        $onlineip = getenv('REMOTE_ADDR');
    } elseif (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
        $onlineip = $_SERVER['REMOTE_ADDR'];
    }
    $token = substr(md5($onlineip), 0, 5);

    @$rawdata = array(
        'code' => $_POST['card'],
        'safeword' => $safeword,
        'software_id' => 44,
        'user_id' => 1,
        'token' => $token,
        'computer_uid' => $onlineip
    );
    $data = json_encode($rawdata);
    $data = openssl_encrypt($data, 'AES-128-CBC', $key, 0, $iv);
    $post_data = array(
        'data' => $data
    );

    // var_dump($post_data);
    // echo send_post('http://localhost/Home/Verify/signin', $post_data);

    $post_data = http_request('http://localhost/Home/Verify/signin', $post_data);
   echo $post_data;
    echo openssl_decrypt($post_data, 'AES-128-CBC', $key, 0, $iv);

/**
* 通用CURL请求
* @param $url  需要请求的url
* @param null $data
* return mixed 返回值 json格式的数据
*/
function http_request($url, $data = null)
{
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    if (!empty($data)) {
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    }
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $info = curl_exec($curl);
    curl_close($curl);
    return $info;
}
    /**
     * 发送post请求
     * @param string $url 请求地址
     * @param array $post_data post键值对数据
     * @return string
     */
    function send_post($url, $post_data)
    {
        $postdata = http_build_query($post_data);
        $options = array(
            'http' => array(
                'method' => 'POST',
                'header' => 'Content-type:application/x-www-form-urlencoded',
                'content' => $postdata,
                'timeout' => 15 * 60 // 超时时间（单位:s）
            )
        );
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);

        return $result;
    }


    ?>
</body>

</html>