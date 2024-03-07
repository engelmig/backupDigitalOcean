<?php

// Define o token de autenticação DigitalOcean
$token = "dop_v1_SEUTOKEN";

// Define a URL base da API DigitalOcean
$apiUrl = 'https://api.digitalocean.com/v2/';

// ID do Droplet que será utilizado para criar o snapshot
$dropletId = "123456789";

// Nome do snapshot a ser criado
$nomeBackup = "NOME_BACKUP";

// Função para criar um snapshot do Droplet
createSnapshot($token, $apiUrl, $dropletId, $nomeBackup);

/**
 * Função para criar um snapshot do Droplet.
 *
 * @param string $token       O token de autenticação DigitalOcean.
 * @param string $apiUrl      A URL base da API DigitalOcean.
 * @param string $dropletId   O ID do Droplet para o qual será criado o snapshot.
 * @param string $nomeBackup  O nome do snapshot a ser criado.
 * @return void
 */
function createSnapshot($token, $apiUrl, $dropletId, $nomeBackup)
{
    // Define os dados para a criação do snapshot
    $data = array(
        "type" => "snapshot",
        "name" => "BACKUP " . $nomeBackup . date('d-M-Y H:i')
    );

    // Inicia a sessão cURL
    $ch = curl_init();

    // Configura as opções da requisição cURL
    curl_setopt($ch, CURLOPT_URL, $apiUrl . 'droplets/' . $dropletId . '/actions');
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Authorization: Bearer ' . $token
    ));
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);

    // Executa a requisição cURL
    curl_exec($ch);

    // Fecha a sessão cURL
    curl_close($ch);
}

/**
 * Função para deletar snapshots desatualizados do Droplet.
 *
 * @param string $token       O token de autenticação DigitalOcean.
 * @param string $apiUrl      A URL base da API DigitalOcean.
 * @param string $dropletId   O ID do Droplet para o qual será deletado o snapshot.
 * @return void
 */
function deleteOutdatedSnapshots($token, $apiUrl, $dropletId)
{
    // Obtém a data atual
    $currentDate = new DateTime();

    // Obtém a lista de snapshots do Droplet
    $snapshots = getSnapshots($token, $apiUrl, $dropletId);

    // Itera sobre os snapshots
    foreach ($snapshots as $snapshot) {
        // Obtém a data de criação do snapshot
        $snapshotDate = new DateTime($snapshot->created_at);
        
        // Modifica a data do snapshot (neste caso, adiciona 3 dias)
        $snapshotDate->modify('+3 days');

        // Verifica se o snapshot é mais antigo que a data atual
        if ($snapshotDate < $currentDate) {
            // Deleta o snapshot
            deleteSnapshot($snapshot->id, $token, $apiUrl, $dropletId);
        }
    }
}

/**
 * Função para deletar um snapshot.
 *
 * @param string $id          O ID do snapshot a ser deletado.
 * @param string $token       O token de autenticação DigitalOcean.
 * @param string $apiUrl      A URL base da API DigitalOcean.
 * @param string $dropletId   O ID do Droplet associado ao snapshot.
 * @return void
 */
function deleteSnapshot($id, $token, $apiUrl, $dropletId)
{
    // Verifica se o ID do snapshot é válido
    if ($id) {
        // Inicia a sessão cURL
        $ch = curl_init();

        // Configura as opções da requisição cURL
        curl_setopt($ch, CURLOPT_URL, $apiUrl . 'images/' . $id);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token
        ));
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);

        // Executa a requisição cURL
        curl_exec($ch);

        // Fecha a sessão cURL
        curl_close($ch);
    }
}

/**
 * Função para obter os snapshots de um Droplet.
 *
 * @param string $token       O token de autenticação DigitalOcean.
 * @param string $apiUrl      A URL base da API DigitalOcean.
 * @param string $dropletId   O ID do Droplet para o qual serão obtidos os snapshots.
 * @return array             A lista de snapshots do Droplet.
 */
function getSnapshots($token, $apiUrl, $dropletId)
{
    // Inicia a sessão cURL
    $ch = curl_init();

    // Configura as opções da requisição cURL
    curl_setopt($ch, CURLOPT_URL, $apiUrl . 'droplets/' . $dropletId . '/snapshots');
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Authorization: Bearer ' . $token
    ));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);

    // Executa a requisição cURL
    $response = curl_exec($ch);

    // Fecha a sessão cURL
    curl_close($ch);

    // Decodifica a resposta JSON
    $result = json_decode($response);

    // Retorna a lista de snapshots, se houver
    if ($result) {
        return $result->snapshots;
    }
    return [];
}

?>
