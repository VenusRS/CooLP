<?php
header('Content-Type: application/json');

/**
 * Function to get Google OAuth 2.0 access token using a service account.
 * @param string $private_key The private key from your Google service account
 * @return string Access token on success, empty string on failure
 */
function getGoogleAccessToken($private_key) {
    $service_account_email = "landingpage@landingpage-405107.iam.gserviceaccount.com";
    $scope = "https://www.googleapis.com/auth/spreadsheets";
    $token_url = "https://oauth2.googleapis.com/token";

    $now = time();
    $jwt_header = base64UrlEncode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
    $jwt_claim_set = base64UrlEncode(json_encode([
        'iss' => $service_account_email,
        'scope' => $scope,
        'aud' => $token_url,
        'exp' => $now + 3600,
        'iat' => $now
    ]));

    // Create the unsigned JWT
    $unsigned_jwt = $jwt_header . '.' . $jwt_claim_set;

    // Sign the JWT with your private key
    $signature = '';
    $success = openssl_sign($unsigned_jwt, $signature, $private_key, 'SHA256');
    if (!$success) {
        return ''; // Failed to sign JWT
    }

    // Base64 encode the signature and create the final JWT
    $jwt = $unsigned_jwt . '.' . base64UrlEncode($signature);

    // Prepare the request to get an access token
    $post_fields = json_encode([
        'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
        'assertion' => $jwt
    ]);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $token_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

    $result = curl_exec($ch);
    if (curl_errno($ch)) {
        return ''; // cURL error occurred
    }

    $response = json_decode($result, true);
    curl_close($ch);

    return $response['access_token'] ?? '';
}

/**
 * Base64 URL encode a string.
 * @param string $data The data to encode
 * @return string The base64 URL encoded data
 */
function base64UrlEncode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Google API Configuration
    $spreadsheetId = '1ZstOQ6UnAn_ba4-zlyGeVHHULxU0zCykXbBgtD4qnBE';
    $api_key = "-----BEGIN PRIVATE KEY-----\nMIIEvAIBADANBgkqhkiG9w0BAQEFAASCBKYwggSiAgEAAoIBAQDcX023Y+JHQpWQ\nKRuI4ZfJ1ndcCW0mmpL5l12e3OEq6kIwHnKkFq28gNi3L+hsIDO2cyyHj/Oyexn9\nAc+7qhT5IR8fcJj3e26GUv/qGXsfxLjz0xCU2AmOBwcxwRU6E7YqfOXSi2lNfTO0\ngWXHLBAa4KUfBiBfgEFz4Oj25S55L8tlGtHc0LDz+Ah+5IMM1UF3kwcDlTi8Ag3c\nh2jpMJMk94q5s/pwPHsCn72FQzW7UO1/T0+IgIchh3yYeOZ+OjXHTcyqLRRWQjoG\nesXxkJk/pO2kExMM2O0yI605IAHDE8MSX6/y4SB+mTmcSwswnwFZC9CVZx1Z9XGf\n2jSxRjApAgMBAAECggEAZu4CvDYkofkZJ4Dz0e7guU75aB0lBMNtA7Qt774mU6eg\nK56FGFxZYLLjxkhTrHEsBjtsYJMdlc9Gt7RpZTOPYT+VrFcos2tNF+Nbkem95vw5\nEPWUCJmReOuScixHsF01mEnHBJzGHgHtLRHFSo6rpQopRcDUTRb4O1ohJfSszL/m\nuKVTIoeHaGW9k11vJKzG/zRQSpFBei/TQidd04ITC+9VAABcxHHY579QJS47wRFE\n6Hkcwh953y5lg7nE323ddijQ3zOxE9sMKkzygGRdtcyrU0VByImOcSBNd9YioIBn\nDMU7PRiM18YdiOeZcDvHobFOV4CE1Tx9NuQJSAhnEwKBgQDycayu6zLNjW8miVtC\n8TX2XvcfwkutZB+DPRAGZ7mBH4VGGyioSxafBjLRokt+sugtv1TPyb9Fi/V0cPll\nuFmNCgXjCMgQ8E/qDDHf6uEtLWF4WhLIQ+4P+kEZ5gJcm+EYRI6WZqT47ZivVKCg\noVQoMJEruu4ssD9lI8qPZCHHFwKBgQDosbIOMA364oVv8HfOCbNgQUn2F50EN17k\nvowDPO8l4BOgiBpWNP/P/QJST2A6zvIhbe1zWEVffEDB1xNhGZ6zL0sXIUZeTiGl\nywx9MPdaSjfXONOlISEISohnPcsIyDeq/Z5AdoNza+27qPLmowCihq+sow0Qtml8\nDu2pH2pKvwKBgFSxXS+lvhOMat29cgIKnV05g362sxUZOuDvvd9e8LCowDfjWOqh\ngH+A/NO6rEDQYsdIZWpJAeZbpB1PMfBU3AOnErNi5/Dy4hfStsGQHaVYiwot/Q20\nnT87nu5bKUwMsC94E496v/qtlX76QzqZ4PpBLRVnsbguwZalUCeTRTF1AoGAEltC\nQqBD2hDYmfYMXXKidetwnDtMpbKAh+cIQJEkBIbixX03JqnTrGK3NisQ2lLNAxoa\n60iBYID60s/WtYhB3rzSXabAWFwth1i3SYD9YmAMe6v99j7gzjii/hH/3Fd/HLwZ\nKdivkgFnpbA0SUF+oVOitCnAtBrPw3uh//98cR0CgYA2RxOOna1eopaRg5ytGSuD\nc9FzHv2N+ooWeib6hSQcQ6u4ab+G9Abmziv/H4OaewNoqkz/04VWYwYKVi/nIt+g\nXPNf36nr0bE+icwwwRjDRPq+V3GoFZvr5+bcLQiaznhonHsykJqupVGg0MMssfyl\nvsh3gJMvCpRKCGgb3B60YQ==\n-----END PRIVATE KEY-----\n";
    $apiEndpoint = 'https://sheets.googleapis.com/v4/spreadsheets/'.$spreadsheetId.'/values/Sheet1:append?valueInputOption=USER_ENTERED';

    $token = getGoogleAccessToken($api_key);
    // Data to be written
    $name = $_POST["name"];
    $email = $_POST["email"];
    $phone = $_POST["tel"];
    $address = $_POST["region"];
    $date = $_POST["date"];
    $url = $_POST["url"];

    // Gather selected question items
    $selectedQuestions = [];
    for ($i = 1; $i <= 3; $i++) {
        if (isset($_POST["question$i"])) {
            $selectedQuestions[] = $_POST["question$i"];
        }
    }

    $data = [[
        $date, 
        $selectedQuestions[0] ?? '', 
        $selectedQuestions[1] ?? '',  
        $selectedQuestions[2] ?? '', 
        $name, 
        $address, 
        $email, 
        $phone, 
        $url,
    ]];

    
    $values = [['values' => $data]];
    // Construct the request payload
    $payload = json_encode(['values' => $data]);

    echo $payload;

    // Set up cURL options
    $options = [
        CURLOPT_URL            => $apiEndpoint,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Authorization: Bearer '.$token,
        ],
        CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
    ];


    // Initialize cURL session
    $ch = curl_init();
    curl_setopt_array($ch, $options);

    // Execute cURL session and close
    $response = curl_exec($ch);

    var_dump($response);

    // Check for cURL errors
    if (curl_errno($ch)) {
        echo json_encode(['status' => 'error', 'message' => 'cURL error: ' . curl_error($ch)]);
        exit;
    }
    
    curl_close($ch);



    // Send confirmation email
    $to = $email;
    $subject = "借金の減額診断に関するご連絡です。";
    $message = "東京新橋法律事務所と申します。\n\nこの度はご診断いただき有難うございました。\n\n診断結果につきましては、後日お電話にて約10-15分ほどでご報告させていただきます。\nまた借金減額診断をさせていただくにあたり、ご相談者の現状の債務状況を改善する為に、分かる範囲で下記内容を教えて頂けますでしょうか？\n\n～質問～\n【１】借入先の『会社名（銀行、クレジットカード含む）』\n【２】各会社の『借入金額（残高）』\n【３】各会社への『毎月の返済額』\n【４】各会社との借入をしてからの期間 ※重要\n\n～回答例～\nアコム 借入60万円 返済2万円\nモビット　　借入80万円　 返済3万円\n○○銀行　　借入120万円 返済4万円\n※上記内容は、わかる範囲で結構ですのでご回答ください\n\n他にも、気になること（困っていること）がございましたら匿名のままで構いませんのでお気軽にご質問ください。\nご回答は必ずご登録頂いたメールアドレスからご連絡を宜しくお願い致します。\n\n今後ともよろしくお願い致します。\n\n-----------------------------------------------\n東京新橋法律事務所\n〒108-0073\n東京都港区三田3-3-8 Tama Woody Gate 三田ビル B1階\nTEL：0120-000-0000\nE-mail：info@tokyo-shinbashi-law.com\n-----------------------------------------------";

    $headers = "From: info@tokyo-shinbashi-law.com";

    if (!mail($to, $subject, $message, $headers)) {
        echo json_encode(['status' => 'error', 'message' => 'Failed to send email.']);
        exit;
    }

    // Check for errors in Google Sheets API request
    $result = @file_get_contents($url, false, $context);

    if ($result === FALSE) {
        $error = error_get_last();
        echo json_encode(['status' => 'error', 'message' => 'Failed to write data to Google Sheets.', 'error' => $error['message']]);
    } else {
        echo json_encode(['status' => 'success', 'message' => 'Data written to Google Sheets and email sent successfully.']);
    }

    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    }
?>
