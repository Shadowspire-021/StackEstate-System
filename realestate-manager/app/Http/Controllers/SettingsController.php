<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Services\GoogleOAuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SettingsController extends Controller
{
    /**
     * Settings configuration schema
     * Defines all allowed settings with their groups, types, and validation rules
     */
    protected array $settingsSchema = [
        // Company Settings
        'company_name' => ['group' => 'company', 'type' => 'text', 'label' => 'Company Name'],
        'company_address' => ['group' => 'company', 'type' => 'textarea', 'label' => 'Company Address'],
        'vendor_name' => ['group' => 'company', 'type' => 'text', 'label' => 'Vendor Name'],
        'vendor_cnic' => ['group' => 'company', 'type' => 'text', 'label' => 'Vendor CNIC'],

        // Google Drive Settings
        'google_root_folder_id' => ['group' => 'google', 'type' => 'text', 'label' => 'Root Folder ID'],
        'google_sheet_id' => ['group' => 'google', 'type' => 'text', 'label' => 'Google Sheet ID'],
        'google_credentials_path' => ['group' => 'google', 'type' => 'text', 'label' => 'Credentials Path'],

        // Notification Settings
        'notification_email_from' => ['group' => 'notifications', 'type' => 'email', 'label' => 'From Email Address'],
        'notification_email_name' => ['group' => 'notifications', 'type' => 'text', 'label' => 'From Name'],
        'notification_enabled' => ['group' => 'notifications', 'type' => 'boolean', 'label' => 'Enable Notifications'],
        'mail_host' => ['group' => 'notifications', 'type' => 'text', 'label' => 'SMTP Host'],
        'mail_port' => ['group' => 'notifications', 'type' => 'number', 'label' => 'SMTP Port'],
        'mail_username' => ['group' => 'notifications', 'type' => 'text', 'label' => 'SMTP Username'],
        'mail_password' => ['group' => 'notifications', 'type' => 'password', 'label' => 'SMTP Password'],
        'mail_encryption' => ['group' => 'notifications', 'type' => 'select', 'label' => 'SMTP Encryption', 'options' => ['tls', 'ssl', 'none']],

        // Backup Settings
        'backup_enabled' => ['group' => 'backup', 'type' => 'boolean', 'label' => 'Enable Automatic Backups'],
        'backup_retention_days' => ['group' => 'backup', 'type' => 'number', 'label' => 'Retention Days'],
        'backup_schedule' => ['group' => 'backup', 'type' => 'text', 'label' => 'Backup Schedule (cron)'],

        // Late Fee Settings
        'late_fee_enabled' => ['group' => 'late_fees', 'type' => 'boolean', 'label' => 'Enable Late Fees'],
        'late_fee_rate' => ['group' => 'late_fees', 'type' => 'number', 'label' => 'Late Fee Rate (%)'],
        'late_fee_period' => ['group' => 'late_fees', 'type' => 'select', 'label' => 'Fee Period', 'options' => ['daily', 'weekly', 'monthly']],
        'late_fee_grace_days' => ['group' => 'late_fees', 'type' => 'number', 'label' => 'Grace Period (days)'],

        // Payment Gateway Settings
        'jazzcash_enabled' => ['group' => 'payment_gateway', 'type' => 'boolean', 'label' => 'Enable JazzCash'],
        'jazzcash_merchant_id' => ['group' => 'payment_gateway', 'type' => 'text', 'label' => 'JazzCash Merchant ID'],
        'easypaisa_enabled' => ['group' => 'payment_gateway', 'type' => 'boolean', 'label' => 'Enable Easypaisa'],
        'easypaisa_merchant_id' => ['group' => 'payment_gateway', 'type' => 'text', 'label' => 'Easypaisa Merchant ID'],
    ];

    /**
     * Display settings page
     */
    public function index()
    {
        // Load settings in both formats for backward compatibility
        $settings = Setting::getAllAsArray();
        $groupedSettings = $this->getGroupedSettings();

        // Google OAuth connection status
        $oauthService = app(GoogleOAuthService::class);
        $oauthConnected = $oauthService->hasDriveToken();
        $driveFolderId = Setting::getValue(GoogleOAuthService::DRIVE_FOLDER_KEY, '');
        $spreadsheetId = Setting::getValue(GoogleOAuthService::SHEETS_SPREADSHEET_KEY, '');

        return view('settings.index', compact(
            'settings',
            'groupedSettings',
            'oauthConnected',
            'driveFolderId',
            'spreadsheetId'
        ));
    }

    /**
     * Update settings
     */
    public function update(Request $request)
    {
        $validationRules = $this->getValidationRules();

        $request->validate($validationRules);

        // Validate google_credentials_path for path traversal
        if ($request->filled('google_credentials_path')) {
            $pathValidation = $this->validateCredentialsPath($request->input('google_credentials_path'));
            if (!$pathValidation['valid']) {
                return back()->withErrors([
                    'google_credentials_path' => $pathValidation['message'],
                ])->withInput();
            }
        }

        // Save settings by group
        $groupedData = $this->extractGroupedData($request);

        foreach ($groupedData as $group => $data) {
            Setting::saveGroup($group, $data);
        }

        return back()->with('success', 'Settings updated successfully.');
    }

    /**
     * Validate credentials path to prevent path traversal attacks.
     *
     * @param  string  $path
     * @return array{valid: bool, message: string|null}
     */
    protected function validateCredentialsPath(string $path): array
    {
        // Reject absolute paths
        if (Str::startsWith($path, '/') || preg_match('/^[a-zA-Z]:[\\\\\/]/', $path)) {
            return ['valid' => false, 'message' => 'Absolute paths are not allowed for credentials file.'];
        }

        // Reject path traversal attempts
        if (str_contains($path, '..')) {
            return ['valid' => false, 'message' => 'Path traversal sequences (..) are not allowed.'];
        }

        // Normalize and check if path is within approved directory
        $normalizedPath = $this->normalizePath($path);
        $approvedBase = storage_path('app/google');

        $realApprovedBase = realpath($approvedBase);
        $realPath = realpath($normalizedPath);

        // If file doesn't exist, check directory
        if ($realPath === false) {
            $realPath = realpath(dirname($normalizedPath));
        }

        if ($realPath === false || $realApprovedBase === false) {
            return ['valid' => false, 'message' => 'Invalid credentials path.'];
        }

        // Ensure path is within approved directory
        if (!Str::startsWith($realPath, $realApprovedBase)) {
            return ['valid' => false, 'message' => 'Credentials file must be within storage/app/google directory.'];
        }

        return ['valid' => true, 'message' => null];
    }

    /**
     * Normalize a file path for the current OS.
     */
    protected function normalizePath(string $path): string
    {
        // Convert to absolute path if relative
        if (!Str::startsWith($path, '/') && !preg_match('/^[a-zA-Z]:[\\\\\/]/', $path)) {
            $path = base_path($path);
        }

        return str_replace('\\', '/', $path);
    }

    /**
     * Get grouped settings for display
     *
     * @return array<string, array{label: string, icon: string, settings: array<string, mixed>}>
     */
    protected function getGroupedSettings(): array
    {
        $dbSettings = Setting::getAllAsArray();

        $groups = [
            'company' => [
                'label' => 'Company Profile',
                'icon' => 'building',
                'settings' => [],
            ],
            'google' => [
                'label' => 'Google Drive',
                'icon' => 'cloud',
                'settings' => [],
            ],
            'notifications' => [
                'label' => 'Notifications',
                'icon' => 'bell',
                'settings' => [],
            ],
            'backup' => [
                'label' => 'Backup & Recovery',
                'icon' => 'archive',
                'settings' => [],
            ],
            'late_fees' => [
                'label' => 'Late Fees',
                'icon' => 'currency-dollar',
                'settings' => [],
            ],
            'payment_gateway' => [
                'label' => 'Payment Gateway',
                'icon' => 'credit-card',
                'settings' => [],
            ],
        ];

        foreach ($this->settingsSchema as $key => $config) {
            $groups[$config['group']]['settings'][$key] = [
                'label' => $config['label'],
                'type' => $config['type'],
                'value' => $dbSettings[$key] ?? '',
            ];
        }

        return $groups;
    }

    /**
     * Get validation rules for all settings
     *
     * @return array<string, string>
     */
    protected function getValidationRules(): array
    {
        return [
            // Company
            'company_name' => 'required|string|max:150',
            'company_address' => 'required|string|max:250',
            'vendor_name' => 'required|string|max:150',
            'vendor_cnic' => 'required|string|max:15',

            // Google Drive
            'google_root_folder_id' => 'nullable|string|max:255',
            'google_sheet_id' => 'nullable|string|max:255',
            'google_credentials_path' => 'nullable|string|max:255',

            // Notifications
            'notification_email_from' => 'nullable|email|max:255',
            'notification_email_name' => 'nullable|string|max:150',
            'notification_enabled' => 'nullable|boolean',
            'mail_host' => 'nullable|string|max:255',
            'mail_port' => 'nullable|integer|min:1|max:65535',
            'mail_username' => 'nullable|string|max:255',
            'mail_password' => 'nullable|string|max:255',
            'mail_encryption' => 'nullable|in:tls,ssl,none',

            // Backup
            'backup_enabled' => 'nullable|boolean',
            'backup_retention_days' => 'nullable|integer|min:1|max:365',
            'backup_schedule' => 'nullable|string|max:100',

            // Late Fees
            'late_fee_enabled' => 'nullable|boolean',
            'late_fee_rate' => 'nullable|numeric|min:0|max:100',
            'late_fee_period' => 'nullable|in:daily,weekly,monthly',
            'late_fee_grace_days' => 'nullable|integer|min:0|max:90',

            // Payment Gateway
            'jazzcash_enabled' => 'nullable|boolean',
            'jazzcash_merchant_id' => 'nullable|string|max:100',
            'easypaisa_enabled' => 'nullable|boolean',
            'easypaisa_merchant_id' => 'nullable|string|max:100',
        ];
    }

    /**
     * Extract grouped data from request
     *
     * @return array<string, array<string, string>>
     */
    protected function extractGroupedData(Request $request): array
    {
        $grouped = [];

        foreach ($this->settingsSchema as $key => $config) {
            $group = $config['group'];

            if (!isset($grouped[$group])) {
                $grouped[$group] = [];
            }

            // Handle boolean values
            if ($config['type'] === 'boolean') {
                $grouped[$group][$key] = $request->has($key) ? '1' : '0';
            } else {
                $grouped[$group][$key] = $request->input($key, '');
            }
        }

        return $grouped;
    }
}
