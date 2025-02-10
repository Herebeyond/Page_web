<?php

    function isImageLinkValid($url) { // vérifie si l'image existe PS: je ne comprends pas comment cette fonction fonctionne, fait par chatGPT
        // Encoder l'URL pour gérer les caractères spéciaux
        $encodedUrl = urlencode($url);
        
        // Décoder les caractères spécifiques qui ne doivent pas être encodés dans une URL
        $encodedUrl = str_replace('%2F', '/', $encodedUrl);
        $encodedUrl = str_replace('%3A', ':', $encodedUrl);
        $encodedUrl = str_replace('%27', "'", $encodedUrl); // Décoder l'apostrophe
    
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 5); // Limite les redirections
        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        curl_close($ch);

        // Vérifie le code HTTP et si le contenu est une image
        return $httpCode === 200 && strpos($contentType, 'image/') !== false;
    }




?>