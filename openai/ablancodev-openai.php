<?php

class Ablancodev_Openai {
    public static function openai_api_request($prompt, $api_key) {
        $url = "https://api.openai.com/v1/chat/completions";
        $data = array(
            "model" => "gpt-4o",
            "messages" => array(
                array("role" => "system", "content" => "Eres un experto recruiter especializado en perfiles IT."),
                array("role" => "user", "content" => $prompt)
            ),
            "response_format" => array( "type" => "json_object" )
        );
    
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json",
            "Authorization: Bearer $api_key"
        ));
    
        $response = curl_exec($ch);
    
        if (!$response) {
            die('Error: "' . curl_error($ch) . '" - Code: ' . curl_errno($ch));
        }
    
        curl_close($ch);
    
        $response_data = json_decode($response, true);

        if ( !isset($response_data['choices']) ) {
            return $response_data['error']['message'];
        }
    
        return $response_data['choices'][0]['message']['content'];
    }

    // enviando una imagen a un endpoint de chatgpt
    /*
    curl https://api.openai.com/v1/chat/completions \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $OPENAI_API_KEY" \
  -d '{
    "model": "gpt-4-turbo",
    "messages": [
      {
        "role": "user",
        "content": [
          {
            "type": "text",
            "text": "What'\''s in this image?"
          },
          {
            "type": "image_url",
            "image_url": {
              "url": "https://upload.wikimedia.org/wikipedia/commons/thumb/d/dd/Gfp-wisconsin-madison-the-nature-boardwalk.jpg/2560px-Gfp-wisconsin-madison-the-nature-boardwalk.jpg"
            }
          }
        ]
      }
    ],
    "max_tokens": 300
  }'

  */
    public static function openai_api_request_image($prompt, $image_url, $api_key) {
        $url = "https://api.openai.com/v1/chat/completions";
        $data = array(
            "model" => "gpt-4o",
            "messages" => array(
                array("role" => "user", "content" => array(
                    array("type" => "text", "text" => $prompt),
                    array("type" => "image_url", "image_url" => array("url" => $image_url))
                ))
            )
        );
    
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json",
            "Authorization: Bearer $api_key"
        ));
    
        $response = curl_exec($ch);
    
        if (!$response) {
            die('Error: "' . curl_error($ch) . '" - Code: ' . curl_errno($ch));
        }
    
        curl_close($ch);
    
        $response_data = json_decode($response, true);

        if ( !isset($response_data['choices']) ) {
            return $response_data['error']['message'];
        }
    
        return $response_data['choices'][0]['message']['content'];
    }

}