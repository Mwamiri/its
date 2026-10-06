<?php

namespace App\Controllers;

use App\Libraries\CredentialVault;
use App\Libraries\DeviceManagement;
use App\Libraries\NetworkMonitor;
use App\Models\CameraModel;
use App\Models\ClientModel;
use App\Models\NetworkDeviceModel;
use App\Models\VaultCredentialModel;
use RuntimeException;
use Throwable;

class ItsNetwork extends ItsBase
{
    private const DEVICE_TYPES = ['router', 'switch', 'access_point', 'firewall', 'server', 'nvr', 'other'];
    private const DEVICE_STATUSES = ['active', 'inactive', 'maintenance'];
    private const CAMERA_TYPES = ['ip', 'ptz', 'analog', 'other'];

    public function index()
    {
        if ($response = $this->needLogin()) {
            return $response;
        }

        $clients = (new ClientModel())->orderBy('name')->findAll();
        $devices = $this->db->table('network_devices')
            ->select('network_devices.*, clients.name AS client_name')
            ->join('clients', 'clients.id = network_devices.client_id')
            ->orderBy('clients.name')
            ->orderBy('network_devices.name')
            ->get()->getResultArray();
        $cameras = $this->db->table('cameras')
            ->select('cameras.*, clients.name AS client_name, network_devices.name AS device_name')
            ->join('clients', 'clients.id = cameras.client_id')
            ->join('network_devices', 'network_devices.id = cameras.network_device_id', 'left')
            ->orderBy('clients.name')
            ->orderBy('cameras.name')
            ->get()->getResultArray();
        $credentials = [];

        if (($this->user()['role'] ?? '') === 'admin') {
            $credentials = $this->db->table('vault_credentials')
                ->select('vault_credentials.id, vault_credentials.client_id, vault_credentials.network_device_id, vault_credentials.label, vault_credentials.username, vault_credentials.notes, vault_credentials.created_at, clients.name AS client_name, network_devices.name AS device_name')
                ->join('clients', 'clients.id = vault_credentials.client_id', 'left')
                ->join('network_devices', 'network_devices.id = vault_credentials.network_device_id', 'left')
                ->orderBy('vault_credentials.label')
                ->get()->getResultArray();
        }

        $deviceModel = new NetworkDeviceModel();
        $cameraModel = new CameraModel();
        $credentialModel = new VaultCredentialModel();
        $revealedCredential = session()->getFlashdata('revealedCredential');
        $deviceOperationResult = session()->getFlashdata('deviceOperationResult');
        if ($revealedCredential) {
            $this->response->setHeader('Cache-Control', 'no-store, private');
        }
        $deviceId = (int) $this->request->getGet('edit_device');
        $cameraId = (int) $this->request->getGet('edit_camera');
        $credentialId = (int) $this->request->getGet('edit_credential');
        $editCamera = $cameraId ? $cameraModel->find($cameraId) : null;
        $rtspUrlError = '';
        if ($editCamera && !empty($editCamera['rtsp_url_ciphertext']) && ($this->user()['role'] ?? '') === 'admin') {
            try {
                $editCamera['rtsp_url'] = (new \App\Libraries\CredentialVault())->decrypt($editCamera['rtsp_url_ciphertext']);
                $this->response->setHeader('Cache-Control', 'no-store, private');
            } catch (Throwable $exception) {
                log_message('error', 'Unable to decrypt RTSP URL for camera {id}: {message}', [
                    'id' => $cameraId,
                    'message' => $exception->getMessage(),
                ]);
                $rtspUrlError = 'The saved RTSP URL could not be decrypted. Verify the application encryption key.';
            }
        }

        return view('itsupport/network', [
            'title' => 'Network & Cameras',
            'clients' => $clients,
            'devices' => $devices,
            'cameras' => $cameras,
            'credentials' => $credentials,
            'editDevice' => $deviceId ? $deviceModel->find($deviceId) : null,
            'editCamera' => $editCamera,
            'rtspUrlError' => $rtspUrlError,
            'editCredential' => $credentialId ? $credentialModel->select('id, client_id, network_device_id, label, username, notes')->find($credentialId) : null,
            'vaultAvailable' => trim((string) config('Encryption')->key) !== '',
            'devicesForSelect' => $deviceModel->orderBy('name')->findAll(),
            'revealedCredential' => $revealedCredential,
            'deviceOperationResult' => $deviceOperationResult,
        ]);
    }

    public function saveDevice(?int $id = null)
    {
        if ($response = $this->needLogin()) {
            return $response;
        }

        $name = $this->postText('name', 190);
        $clientId = $this->postInt('client_id');
        $type = $this->postText('device_type', 40);
        $status = $this->postText('status', 30);
        if ($name === '' || !$this->clientExists($clientId) || !in_array($type, self::DEVICE_TYPES, true) || !in_array($status, self::DEVICE_STATUSES, true)) {
            return $this->back('network', 'Enter a device name and choose a valid client, device type, and status.');
        }

        $data = [
            'client_id' => $clientId,
            'name' => mb_substr($name, 0, 190),
            'device_type' => $type,
            'hostname' => $this->postText('hostname', 190),
            'ip_address' => $this->postText('ip_address', 45),
            'mac_address' => $this->postText('mac_address', 32),
            'manufacturer' => $this->postText('manufacturer', 120),
            'model' => $this->postText('model', 120),
            'location' => $this->postText('location', 190),
            'status' => $status,
            'notes' => $this->postText('notes', 10000),
            'monitor_enabled' => $this->request->getPost('monitor_enabled') === '1' ? 1 : 0,
        ];
        $monitorPort = $this->request->getPost('monitor_port');
        if (is_string($monitorPort) && $monitorPort !== '') {
            if (!ctype_digit($monitorPort) || (int) $monitorPort < 1 || (int) $monitorPort > 65535) {
                return $this->back('network', 'Monitoring port must be between 1 and 65535.');
            }
            $data['monitor_port'] = (int) $monitorPort;
        } else {
            $data['monitor_port'] = null;
        }

        $model = new NetworkDeviceModel();
        if ($id) {
            if (!$model->find($id)) {
                return $this->back('network', 'That network device no longer exists.');
            }
            $model->update($id, $data);
            $this->audit('network_device_updated', 'network', $data['name']);
            $message = 'Network device updated.';
        } else {
            $model->insert($data);
            $this->audit('network_device_created', 'network', $data['name']);
            $message = 'Network device added.';
        }

        return redirect()->to(base_url('its-network'))->with('ok', $message);
    }

    public function checkDevice(int $id)
    {
        if ($response = $this->needAdmin()) {
            return $response;
        }
        $device = (new NetworkDeviceModel())->find($id);
        if (!$device) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Device not found.']);
        }
        $result = (new NetworkMonitor())->check($device);
        (new NetworkDeviceModel())->update($id, [
            'monitor_state' => $result['state'],
            'monitor_port' => $result['port'],
            'last_checked' => $result['last_checked'],
            'last_seen' => $result['last_seen'],
        ]);
        $this->audit('network_device_checked', 'network', $device['name'] . ': ' . $result['state']);
        $this->response->setHeader('Cache-Control', 'no-store, private');
        return $this->response->setJSON([
            'name' => $device['name'],
            'state' => $result['state'],
            'last_checked' => $result['last_checked'],
            'error' => $result['error'],
        ]);
    }

    public function deviceAction(int $id)
    {
        if ($response = $this->needAdmin()) {
            return $response;
        }
        $device = (new NetworkDeviceModel())->find($id);
        if (!$device) {
            return $this->back('network', 'That network device no longer exists.');
        }
        $action = $this->postText('action', 20);
        if ($action === 'reboot' && $this->request->getPost('confirm_action') !== 'REBOOT') {
            return $this->back('network', 'Confirm the reboot action before continuing.');
        }

        $manufacturer = strtolower((string) ($device['manufacturer'] ?? ''));
        $provider = str_contains($manufacturer, 'mikrotik') ? 'routeros' : (str_contains($manufacturer, 'hikvision') ? 'hikvision' : '');
        $allowed = [
            'routeros' => ['reboot', 'leases', 'wifi'],
            'hikvision' => ['reboot', 'storage'],
        ];
        if ($provider === '' || !in_array($action, $allowed[$provider], true)) {
            return $this->back('network', 'That operation is not supported for this device manufacturer.');
        }

        $credential = $this->db->table('vault_credentials')
            ->where('network_device_id', $id)
            ->orderBy('id', 'DESC')
            ->get()->getRowArray();
        if (!$credential) {
            return $this->back('network', 'Add a credential-vault entry linked to this device before using remote management.');
        }

        try {
            $password = $this->vault->decrypt($credential['secret_ciphertext']);
            $management = new DeviceManagement();
            if ($provider === 'routeros') {
                $result = $management->routerOs($device, (string) $credential['username'], $password, $action);
                $result = array_values(array_filter($result, static fn(array $row): bool => ($row['_type'] ?? '') === '!re'));
                $visible = array_map(static function (array $row): array {
                    return array_intersect_key($row, array_flip(['name', 'address', 'mac-address', 'server', 'status', 'uptime', 'comment']));
                }, $result);
                $message = $action === 'reboot' ? 'RouterOS reboot command accepted.' : json_encode($visible, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
            } else {
                $responseBody = $management->hikvision($device, (string) $credential['username'], $password, $action);
                $message = $action === 'reboot' ? 'Hikvision reboot command accepted.' : 'Hikvision storage status received: ' . mb_substr(strip_tags($responseBody), 0, 1500);
            }
        } catch (Throwable $exception) {
            log_message('error', 'Network operation {action} failed for device {id}: {message}', [
                'action' => $action,
                'id' => $id,
                'message' => $exception->getMessage(),
            ]);
            return $this->back('network', 'Remote operation failed: ' . $exception->getMessage());
        }

        $this->audit('network_device_' . $action, 'network', $device['name']);
        if ($action !== 'reboot' && $provider === 'routeros') {
            session()->setFlashdata('deviceOperationResult', ['device' => $device['name'], 'action' => $action, 'result' => $message]);
        }
        return redirect()->to(base_url('its-network'))->with('ok', $message);
    }

    public function deleteDevice(int $id)
    {
        if ($response = $this->needLogin()) {
            return $response;
        }

        $model = new NetworkDeviceModel();
        $device = $model->find($id);
        if (!$device) {
            return $this->back('network', 'That network device no longer exists.');
        }
        if ($this->db->table('cameras')->where('network_device_id', $id)->countAllResults() > 0
            || $this->db->table('vault_credentials')->where('network_device_id', $id)->countAllResults() > 0) {
            return $this->back('network', 'Unlink the cameras and vault credentials before deleting this device.');
        }

        $model->delete($id);
        $this->audit('network_device_deleted', 'network', $device['name']);
        return redirect()->to(base_url('its-network'))->with('ok', 'Network device deleted.');
    }

    public function saveCamera(?int $id = null)
    {
        if ($response = $this->needLogin()) {
            return $response;
        }

        $name = $this->postText('name', 190);
        $clientId = $this->postInt('client_id');
        $deviceId = $this->postInt('network_device_id');
        $type = $this->postText('camera_type', 40);
        $status = $this->postText('status', 30);
        if ($name === '' || !$this->clientExists($clientId) || !in_array($type, self::CAMERA_TYPES, true) || !in_array($status, self::DEVICE_STATUSES, true)
            || ($deviceId && !$this->deviceBelongsToClient($deviceId, $clientId))) {
            return $this->back('network', 'Enter a camera name and choose valid client, camera type, device, and status values.');
        }

        $data = [
            'client_id' => $clientId,
            'network_device_id' => $deviceId ?: null,
            'name' => mb_substr($name, 0, 190),
            'camera_type' => $type,
            'ip_address' => $this->postText('ip_address', 45),
            'location' => $this->postText('location', 190),
            'recorder' => $this->postText('recorder', 190),
            'channel_number' => $this->postText('channel_number', 40),
            'status' => $status,
            'notes' => $this->postText('notes', 10000),
        ];
        if (($this->user()['role'] ?? '') === 'admin') {
            if ($this->request->getPost('remove_rtsp_url') === '1') {
                $data['rtsp_url_ciphertext'] = null;
            } else {
                $rtspUrl = $this->postText('rtsp_url', 2048);
                if ($rtspUrl !== '') {
                    if (!$this->validRtspUrl($rtspUrl)) {
                        return $this->back('network', 'Enter a valid RTSP or RTSPS URL (maximum 2,048 characters).');
                    }
                    try {
                        $data['rtsp_url_ciphertext'] = (new \App\Libraries\CredentialVault())->encrypt($rtspUrl);
                    } catch (RuntimeException $exception) {
                        log_message('error', 'Unable to encrypt RTSP URL for camera: {message}', ['message' => $exception->getMessage()]);
                        return $this->back('network', 'Configure the application encryption key before saving an RTSP URL.');
                    }
                }
            }
        }
        $model = new CameraModel();
        if ($id) {
            if (!$model->find($id)) {
                return $this->back('network', 'That camera no longer exists.');
            }
            $model->update($id, $data);
            $this->audit('camera_updated', 'cameras', $data['name']);
            $message = 'Camera updated.';
        } else {
            $model->insert($data);
            $this->audit('camera_created', 'cameras', $data['name']);
            $message = 'Camera added.';
        }

        return redirect()->to(base_url('its-network'))->with('ok', $message);
    }

    public function deleteCamera(int $id)
    {
        if ($response = $this->needLogin()) {
            return $response;
        }

        $model = new CameraModel();
        $camera = $model->find($id);
        if (!$camera) {
            return $this->back('network', 'That camera no longer exists.');
        }
        $model->delete($id);
        $this->audit('camera_deleted', 'cameras', $camera['name']);
        return redirect()->to(base_url('its-network'))->with('ok', 'Camera deleted.');
    }

    public function saveCredential(?int $id = null)
    {
        if ($response = $this->needAdmin()) {
            return $response;
        }

        $label = $this->postText('label', 190);
        $secretValue = $this->request->getPost('secret');
        $secret = is_string($secretValue) ? $secretValue : '';
        $clientId = $this->postInt('client_id');
        $deviceId = $this->postInt('network_device_id');
        if ($label === '' || strlen($secret) > 5000 || ($clientId && !$this->clientExists($clientId))
            || ($deviceId && (!$clientId || !$this->deviceBelongsToClient($deviceId, $clientId)))) {
            return $this->back('network', 'Enter a credential label and select a matching client and device. Secrets must be under 5,000 bytes.');
        }

        $model = new VaultCredentialModel();
        $existing = $id ? $model->find($id) : null;
        if ($id && !$existing) {
            return $this->back('network', 'That vault entry no longer exists.');
        }
        if (!$existing && $secret === '') {
            return $this->back('network', 'Enter a secret for the new vault entry.');
        }

        try {
            $data = [
                'client_id' => $clientId ?: null,
                'network_device_id' => $deviceId ?: null,
                'label' => mb_substr($label, 0, 190),
                'username' => $this->postText('username', 190),
                'notes' => $this->postText('notes', 10000),
                'updated_at' => date('Y-m-d H:i:s'),
            ];
            if ($secret !== '') {
                $data['secret_ciphertext'] = (new CredentialVault())->encrypt($secret);
            }
        } catch (Throwable $exception) {
            log_message('error', 'Credential vault encryption failed: {message}', ['message' => $exception->getMessage()]);
            return $this->back('network', 'The credential could not be encrypted. Check encryption.key in .env and try again.');
        }

        if ($existing) {
            $model->update($id, $data);
            $this->audit('vault_credential_updated', 'vault', $data['label']);
            $message = 'Vault entry updated.';
        } else {
            $data['created_by'] = (int) $this->user()['id'];
            $data['created_at'] = date('Y-m-d H:i:s');
            $model->insert($data);
            $this->audit('vault_credential_created', 'vault', $data['label']);
            $message = 'Vault entry saved encrypted.';
        }

        return redirect()->to(base_url('its-network'))->with('ok', $message);
    }

    public function revealCredential(int $id)
    {
        if ($response = $this->needAdmin()) {
            return $response;
        }

        $entry = (new VaultCredentialModel())->select('id, secret_ciphertext')->find($id);
        if (!$entry) {
            return $this->back('network', 'That vault entry no longer exists.');
        }

        try {
            $secret = (new CredentialVault())->decrypt($entry['secret_ciphertext']);
        } catch (Throwable $exception) {
            log_message('error', 'Credential vault decryption failed for entry {id}: {message}', [
                'id' => $id,
                'message' => $exception->getMessage(),
            ]);
            return $this->back('network', 'The secret could not be decrypted. Verify the configured encryption key.');
        }

        session()->setFlashdata('revealedCredential', ['id' => $id, 'secret' => $secret]);
        $this->audit('vault_credential_revealed', 'vault', 'Credential ID ' . $id);
        return redirect()->to(base_url('its-network#credential-vault'));
    }

    public function deleteCredential(int $id)
    {
        if ($response = $this->needAdmin()) {
            return $response;
        }

        $model = new VaultCredentialModel();
        $entry = $model->find($id);
        if (!$entry) {
            return $this->back('network', 'That vault entry no longer exists.');
        }
        $model->delete($id);
        $this->audit('vault_credential_deleted', 'vault', $entry['label']);
        return redirect()->to(base_url('its-network#credential-vault'))->with('ok', 'Vault entry deleted.');
    }

    private function clientExists(int $clientId): bool
    {
        return $clientId > 0 && (new ClientModel())->find($clientId) !== null;
    }

    private function deviceBelongsToClient(int $deviceId, int $clientId): bool
    {
        return $this->db->table('network_devices')->where('id', $deviceId)->where('client_id', $clientId)->countAllResults() === 1;
    }

    private function validRtspUrl(string $url): bool
    {
        if (strlen($url) > 2048 || preg_match('/[\x00-\x20\x7f]/', $url)) {
            return false;
        }
        $parts = parse_url($url);
        return is_array($parts)
            && in_array(strtolower((string) ($parts['scheme'] ?? '')), ['rtsp', 'rtsps'], true)
            && isset($parts['host'])
            && $parts['host'] !== ''
            && (!isset($parts['port']) || ($parts['port'] >= 1 && $parts['port'] <= 65535));
    }

    private function postText(string $key, int $maxLength): string
    {
        $value = $this->request->getPost($key);
        return is_string($value) ? mb_substr(trim($value), 0, $maxLength) : '';
    }

    private function postInt(string $key): int
    {
        $value = $this->request->getPost($key);
        return is_string($value) && ctype_digit($value) ? (int) $value : 0;
    }

    private function back(string $tab, string $message)
    {
        return redirect()->to(base_url('its-network#' . $tab))->with('err', $message);
    }
}
