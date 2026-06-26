<?php
function accounting($ul, $pt, $lc, $em, $un, $type = 1, $pid = null)
{
    $ch = curl_init();
    $request_url = ($type == 1) ? base64_decode(config('accounting.lic1')) : base64_decode(config('accounting.lic2'));
   
    $pid = is_null($pid) ? config('accounting.pid') : $pid;

    $curlConfig = [CURLOPT_URL => $request_url,
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_POSTFIELDS     => [
            'url' => $ul,
            'path' => $pt,
            'license_code' => $lc,
            'email' => $em,
            'username' => $un,
            'product_id' => $pid
        ]
    ];
    curl_setopt_array($ch, $curlConfig);
    $result = curl_exec($ch);

    if (curl_errno($ch)) {
        $error_msg = 'C'.'U'.'RL '.'E'.'rro'.'r: ';
        $error_msg .= curl_errno($ch);

        return redirect()->back()
            ->with('error', $error_msg);
    }
    curl_close($ch);

    if ($result) {
        $result = json_decode($result, true);

        if ($result['flag'] == 'valid') {
            // if(!empty($result['data'])){
            //     $this->_handle_data($result['data']);
            // }
        } else {
            $msg = (isset($result['msg']) && !empty($result['msg'])) ? $result['msg'] : "I"."nvali"."d "."Lic"."ense Det"."ails";
            return redirect()->back()
                ->with('error', $msg);
        }
    }
}