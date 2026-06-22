<?php

namespace App\Services;

use App\Models\User;
use App\Models\Customer;
use Illuminate\Support\Facades\Log;

class MikrotikService
{
    protected $api;

    public function __construct()
    {
        $this->api = new RouterosAPI();
    }

    /**
     * Get connection to MikroTik router for an admin user
     *
     * @param User $adminUser
     * @return boolean
     */
    public function connect(User $adminUser): bool
    {
        $host = $adminUser->mikrotik_host;
        $username = $adminUser->mikrotik_username;
        $password = $adminUser->mikrotik_password;
        $port = $adminUser->mikrotik_port ?: 8728;

        if (empty($host) || empty($username)) {
            return false;
        }

        // Auto-split jika data di DB berisi IP:Port
        if (str_contains($host, ':')) {
            $parts = explode(':', $host);
            $host = $parts[0];
            $port = (int)$parts[1];
        }

        return $this->api->connect($host, $username, $password, $port);
    }

    /**
     * Disconnect connection
     */
    public function disconnect(): void
    {
        $this->api->disconnect();
    }

    /**
     * Get Router System Resources
     *
     * @return array
     */
    public function getSystemResources(): array
    {
        $resources = $this->api->comm('/system/resource/print');
        $routerboard = $this->api->comm('/system/routerboard/print');
        
        if (empty($resources) || isset($resources['trap'])) {
            return [];
        }

        $res = $resources[0] ?? [];
        $board = $routerboard[0] ?? [];

        return [
            'uptime' => $res['uptime'] ?? 'Unknown',
            'cpu_load' => (int)($res['cpu-load'] ?? 0),
            'free_memory' => (int)($res['free-memory'] ?? 0),
            'total_memory' => (int)($res['total-memory'] ?? 0),
            'version' => $res['version'] ?? 'Unknown',
            'board_name' => $res['board-name'] ?? $board['model'] ?? 'RouterOS Device',
            'cpu_frequency' => $res['cpu-frequency'] ?? 'Unknown',
        ];
    }

    /**
     * Get Connected Active Secrets (PPPoE / Hotspot) — enriched with Customer data from DB.
     *
     * @return array
     */
    public function getActiveUsers(): array
    {
        $pppoeActive   = $this->api->comm('/ppp/active/print');
        $hotspotActive = $this->api->comm('/ip/hotspot/active/print');

        $active = [];

        // Preload all customers with their packages untuk matching
        $customers = \App\Models\Customer::with('package')->get();

        /**
         * Helper: cari customer berdasarkan username PPPoE/Hotspot.
         * Strategi: ekstrak angka di awal username (customer_code) atau match langsung.
         */
        $findCustomer = function (string $username) use ($customers): ?object {
            $usernameLower = strtolower($username);

            foreach ($customers as $c) {
                // Pencocokan 1: username dimulai dengan customer_code (misal "1603tuminemtlogo")
                $code = strtolower($c->customer_code ?? '');
                if ($code && str_starts_with($usernameLower, $code)) {
                    return $c;
                }

                // Pencocokan 2: nama pelanggan cocok (partial, case-insensitive)
                $name = strtolower($c->name ?? '');
                if ($name && str_contains($usernameLower, $name)) {
                    return $c;
                }
            }

            // Pencocokan 3: ekstrak angka di awal sebagai customer_code
            if (preg_match('/^(\d+)/', $username, $m)) {
                $extractedCode = $m[1];
                foreach ($customers as $c) {
                    if ($c->customer_code === $extractedCode) {
                        return $c;
                    }
                }
            }

            return null;
        };

        if (!empty($pppoeActive) && !isset($pppoeActive['trap'])) {
            foreach ($pppoeActive as $u) {
                $username = $u['name'] ?? 'Unknown';
                $matched  = $findCustomer($username);

                $active[] = [
                    'name'          => $username,
                    'address'       => $u['address'] ?? '-',
                    'uptime'        => $u['uptime'] ?? '-',
                    'service'       => 'PPPoE',
                    'caller_id'     => $u['caller-id'] ?? '-',
                    // Enriched customer data
                    'customer_id'   => $matched?->id,
                    'customer_code' => $matched?->customer_code ?? '-',
                    'customer_name' => $matched?->name ?? null,
                    'customer_phone'=> $matched?->phone ?? null,
                    'package_name'  => $matched?->package?->name ?? null,
                    'is_active'     => $matched ? (bool)($matched->is_active ?? true) : null,
                ];
            }
        }

        if (!empty($hotspotActive) && !isset($hotspotActive['trap'])) {
            foreach ($hotspotActive as $u) {
                $username = $u['user'] ?? 'Unknown';
                $matched  = $findCustomer($username);

                $active[] = [
                    'name'          => $username,
                    'address'       => $u['address'] ?? '-',
                    'uptime'        => $u['uptime'] ?? '-',
                    'service'       => 'Hotspot',
                    'caller_id'     => $u['mac-address'] ?? '-',
                    'customer_id'   => $matched?->id,
                    'customer_code' => $matched?->customer_code ?? '-',
                    'customer_name' => $matched?->name ?? null,
                    'customer_phone'=> $matched?->phone ?? null,
                    'package_name'  => $matched?->package?->name ?? null,
                    'is_active'     => $matched ? (bool)($matched->is_active ?? true) : null,
                ];
            }
        }

        return $active;
    }



    /**
     * Get ALL raw entries from IP Firewall Address List (no database matching).
     * Used for syncing MikroTik → Database.
     *
     * @return array
     */
    public function getAllAddressListEntries(): array
    {
        $addressLists = $this->api->comm('/ip/firewall/address-list/print');
        
        if (empty($addressLists) || isset($addressLists['trap'])) {
            return [];
        }

        return $addressLists;
    }

    /**
     * Get IP Firewall Address List and match with Database Customers by IP / ID.
     * Filter by matching 4-digit code (or digits starting device/list name).
     *
     * @return array
     */
    public function getMatchedAddressLists(): array
    {
        $addressLists = $this->api->comm('/ip/firewall/address-list/print');
        
        if (empty($addressLists) || isset($addressLists['trap'])) {
            return [];
        }

        // Ambil data pelanggan dari database
        $customers = Customer::with('package')->get();

        $matched = [];

        foreach ($addressLists as $entry) {
            $listName = $entry['list'] ?? '';
            $ipAddress = $entry['address'] ?? '';
            $comment = $entry['comment'] ?? '';
            $disabled = ($entry['disabled'] ?? 'false') === 'true';

            // Kriteria pencocokan ID Pelanggan (dimulai dengan angka / 4 digit angka, misal: 1002 - John)
            // Cek apakah list name atau comment mengandung ID berupa digit (minimal 3 atau 4 digit)
            $extractedId = null;
            $matches = [];
            
            if (preg_match('/^(\d+)/', $listName, $matches)) {
                $extractedId = $matches[1];
            } elseif (preg_match('/^(\d+)/', $comment, $matches)) {
                $extractedId = $matches[1];
            } elseif (preg_match('/(\d{4})/', $listName, $matches)) {
                $extractedId = $matches[1];
            } elseif (preg_match('/(\d{4})/', $comment, $matches)) {
                $extractedId = $matches[1];
            }

            // Cari pelanggan yang cocok
            $matchedCustomer = null;
            foreach ($customers as $c) {
                // Pencocokan 1: Berdasarkan Alamat IP
                if (!empty($ipAddress) && $c->ip === $ipAddress) {
                    $matchedCustomer = $c;
                    break;
                }
                // Pencocokan 2: Berdasarkan Kode Pelanggan (jika ter-ekstrak)
                if (!empty($extractedId) && str_contains($c->customer_code, $extractedId)) {
                    $matchedCustomer = $c;
                    break;
                }
                // Pencocokan 3: Kode Pelanggan langsung sama dengan nama list atau komentar
                if ($c->customer_code === $listName || $c->customer_code === $comment) {
                    $matchedCustomer = $c;
                    break;
                }
            }

            // Hanya masukkan jika ada kecocokan atau jika list name / comment diawali angka (persyaratan seperti PRTG)
            $isDigitStarted = preg_match('/^\d+/', $listName) || preg_match('/^\d+/', $comment);
            
            if ($matchedCustomer || $isDigitStarted) {
                $matched[] = [
                    'mikrotik_id'      => $entry['.id'] ?? '',
                    'list'             => $listName,
                    'address'          => $ipAddress,
                    'comment'          => $comment,
                    'disabled'         => $disabled,
                    'customer_id'      => $matchedCustomer?->id,
                    'customer_code'    => $matchedCustomer?->customer_code ?? $extractedId ?? '-',
                    'customer_name'    => $matchedCustomer?->name ?? 'Device Non-DB (' . ($listName ?: $comment) . ')',
                    'customer_phone'   => $matchedCustomer?->phone ?? '-',
                    'customer_address' => $matchedCustomer?->address ?? '-',
                    'package_name'     => $matchedCustomer?->package?->name ?? '-',
                    'is_active'        => $matchedCustomer ? (bool)($matchedCustomer->is_active ?? true) : null,
                ];
            }

        }

        return $matched;
    }

    /**
     * Enable or disable customer address list entries on MikroTik
     *
     * @param User $adminUser
     * @param string $customerCode
     * @param string|null $ipAddress
     * @param bool $enable
     * @return bool
     */
    public function setCustomerNetworkStatus(User $adminUser, string $customerCode, ?string $ipAddress, bool $enable): bool
    {
        if (!$this->connect($adminUser)) {
            return false;
        }

        // Ambil semua entri address list untuk dicocokkan
        $addressLists = $this->api->comm('/ip/firewall/address-list/print');
        if (empty($addressLists) || isset($addressLists['trap'])) {
            $this->disconnect();
            return false;
        }

        $success = false;

        foreach ($addressLists as $entry) {
            $listName = $entry['list'] ?? '';
            $address = $entry['address'] ?? '';
            $comment = $entry['comment'] ?? '';
            $id = $entry['.id'] ?? null;

            if (!$id) continue;

            $matches = false;

            // Pencocokan 1: Berdasarkan IP Address
            if (!empty($ipAddress) && $address === $ipAddress) {
                $matches = true;
            }
            // Pencocokan 2: Berdasarkan Kode Pelanggan (dimulai atau mengandung kode pelanggan)
            elseif (str_starts_with($listName, $customerCode) || str_starts_with($comment, $customerCode)) {
                $matches = true;
            }

            if ($matches) {
                // $enable = true (internet aktif) => disabled = false (RouterOS: yes/no, or true/false)
                $disabledStr = $enable ? 'false' : 'true';
                
                $response = $this->api->comm('/ip/firewall/address-list/set', [
                    '.id' => $id,
                    'disabled' => $disabledStr
                ]);

                if (!isset($response['trap'])) {
                    $success = true;
                }
            }
        }

        $this->disconnect();
        return $success;
    }

    /**
     * Remove customer entries from RouterOS address-list
     *
     * @param User $adminUser
     * @param string $customerCode
     * @param string|null $ipAddress
     * @return bool
     */
    public function removeCustomerFromAddressList(User $adminUser, string $customerCode, ?string $ipAddress): bool
    {
        if (!$this->connect($adminUser)) {
            return false;
        }

        $addressLists = $this->api->comm('/ip/firewall/address-list/print');
        if (empty($addressLists) || isset($addressLists['trap'])) {
            $this->disconnect();
            return false;
        }

        $success = false;

        foreach ($addressLists as $entry) {
            $listName = $entry['list'] ?? '';
            $address = $entry['address'] ?? '';
            $comment = $entry['comment'] ?? '';
            $id = $entry['.id'] ?? null;

            if (!$id) continue;

            $matches = false;

            // Pencocokan berdasarkan IP
            if (!empty($ipAddress) && $address === $ipAddress) {
                $matches = true;
            }
            // Pencocokan berdasarkan kode pelanggan di comment atau list name
            elseif (str_starts_with($comment, $customerCode) || str_starts_with($listName, $customerCode)) {
                $matches = true;
            }

            if ($matches) {
                $response = $this->api->comm('/ip/firewall/address-list/remove', ['.id' => $id]);
                if (!isset($response['trap'])) {
                    $success = true;
                    Log::info("MikroTik: Berhasil menghapus entri address-list '{$comment}' ({$address}) dari MikroTik");
                }
            }
        }

        $this->disconnect();
        return $success;
    }

    /**
     * Ensure customer exists in RouterOS address-list and disabled status matches their DB state
     *
     * @param User $adminUser
     * @param Customer $customer
     * @return bool
     */
    public function ensureCustomerInAddressList(User $adminUser, Customer $customer): bool
    {
        if (empty($customer->ip)) {
            return false;
        }

        if (!$this->connect($adminUser)) {
            return false;
        }

        try {
            $addressLists = $this->api->comm('/ip/firewall/address-list/print');
            if (empty($addressLists) || isset($addressLists['trap'])) {
                if (isset($addressLists['trap'])) {
                    return false;
                }
                $addressLists = [];
            }

            $matchedEntry = null;
            $customerCode = $customer->customer_code;
            $ipAddress = $customer->ip;

            foreach ($addressLists as $entry) {
                $listName = $entry['list'] ?? '';
                $address = $entry['address'] ?? '';
                $comment = $entry['comment'] ?? '';

                if ($address === $ipAddress || 
                    str_starts_with($listName, $customerCode) || 
                    str_starts_with($comment, $customerCode)) {
                    $matchedEntry = $entry;
                    break;
                }
            }

            $isActive = (bool)($customer->is_active ?? true);
            $disabledVal = $isActive ? 'false' : 'true';

            if ($matchedEntry) {
                $id = $matchedEntry['.id'];
                // Hanya update status disabled/enabled dan IP jika berubah
                // TIDAK mengubah nama list atau comment yang sudah ada di MikroTik
                $params = [
                    '.id' => $id,
                    'disabled' => $disabledVal,
                ];

                if (($matchedEntry['address'] ?? '') !== $ipAddress) {
                    $params['address'] = $ipAddress;
                }

                $response = $this->api->comm('/ip/firewall/address-list/set', $params);
                if (isset($response['trap'])) {
                    return false;
                }
                return true;
            } else {
                // IP belum ada di address-list MikroTik → daftarkan entri baru
                $commentVal = $customerCode . ' - ' . $customer->name;

                $response = $this->api->comm('/ip/firewall/address-list/add', [
                    'list' => 'Jernih_Via',
                    'address' => $ipAddress,
                    'comment' => $commentVal,
                    'disabled' => $disabledVal,
                ]);

                if (isset($response['trap'])) {
                    Log::error("MikroTik: Gagal mendaftarkan IP '{$ipAddress}' ke address-list. Error: " . ($response['trap']['message'] ?? 'Unknown'));
                    return false;
                }

                Log::info("MikroTik: Berhasil mendaftarkan pelanggan '{$customer->name}' ({$ipAddress}) ke address-list Jernih_Via dengan comment '{$commentVal}'");
                return true;
            }
        } finally {
            $this->disconnect();
        }
    }

    /**
     * Get Router Interfaces — hanya physical ports (ether, sfp, bond) dan VLAN.
     * PPPoE, bridge, loopback, dan tunnel dikecualikan.
     *
     * @return array
     */
    public function getInterfaces(): array
    {
        $interfaces = $this->api->comm('/interface/print');
        
        if (empty($interfaces) || isset($interfaces['trap'])) {
            return [];
        }

        $result = [];
        foreach ($interfaces as $iface) {
            $type = strtolower($iface['type'] ?? '');
            $name = $iface['name'] ?? '';
            $nameLower = strtolower($name);

            // Kecualikan semua tipe PPPoE / PPP / tunnel / loopback
            $excludedTypes = ['pppoe-in', 'pppoe-out', 'ppp', 'pptp-in', 'pptp-out', 'l2tp-in', 'l2tp-out', 'sstp-in', 'sstp-out', 'ovpn-in', 'ovpn-out', 'loopback', 'wg'];
            if (in_array($type, $excludedTypes)) {
                continue;
            }

            // Hanya sertakan interface dengan nama diawali ether, sfp, bond, atau vlan
            // Ini adalah satu-satunya cara yang reliable untuk memilih port & VLAN
            $isWanted = (bool) preg_match('/^(ether|sfp|bond|vlan)/i', $name);
            if (!$isWanted) {
                continue;
            }

            $result[] = [
                'id'       => $iface['.id'] ?? '',
                'name'     => $name,
                'type'     => $iface['type'] ?? 'Unknown',
                'running'  => ($iface['running'] ?? 'false') === 'true',
                'disabled' => ($iface['disabled'] ?? 'false') === 'true',
                'comment'  => $iface['comment'] ?? '',
                'rx_byte'  => (int)($iface['rx-byte'] ?? 0),
                'tx_byte'  => (int)($iface['tx-byte'] ?? 0),
            ];
        }

        return $result;
    }

    /**
     * Get traffic bytes for a single named interface only.
     * Digunakan untuk polling efisien — hanya return data interface yang dipilih.
     *
     * @param string $interfaceName
     * @return array|null  ['name', 'rx_byte', 'tx_byte', 'running'] or null if not found
     */
    public function getInterfaceTraffic(string $interfaceName): ?array
    {
        // Catatan: library RouterosAPI ini tidak mendukung query filter (?name=xxx)
        // karena menambahkan prefix '=' yang salah. Kita fetch semua dan filter manual.
        $interfaces = $this->api->comm('/interface/print');

        if (empty($interfaces) || isset($interfaces['trap'])) {
            return null;
        }

        foreach ($interfaces as $iface) {
            if (($iface['name'] ?? '') === $interfaceName) {
                return [
                    'name'    => $iface['name'],
                    'type'    => $iface['type'] ?? 'Unknown',
                    'running' => ($iface['running'] ?? 'false') === 'true',
                    'rx_byte' => (int)($iface['rx-byte'] ?? 0),
                    'tx_byte' => (int)($iface['tx-byte'] ?? 0),
                ];
            }
        }

        return null;
    }
}
