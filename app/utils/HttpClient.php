<?php
class HttpClient {
    /**
     * Executa uma requisição GET na URL informada com os headers fornecidos.
     */
    public static function get(string $url, array $headers = []): ?string {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        if (!empty($headers)) {
            $formattedHeaders = [];
            foreach ($headers as $key => $value) {
                $formattedHeaders[] = $key . ": " . $value;
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, $formattedHeaders);
        }
        $html = curl_exec($ch);
        if ($html === false) {
            debug_log("[HttpClient] | Erro ao obter HTML de $url: " . curl_error($ch));
            curl_close($ch);
            return null;
        }
        curl_close($ch);
        return $html;
    }
}
?>
