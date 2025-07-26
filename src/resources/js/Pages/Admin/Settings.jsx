import AdminLayout from '@/Layouts/AdminLayout';
import { Head, router } from '@inertiajs/react';
import { useState } from 'react';

export default function Settings({ auth }) {
    const [generalSettings, setGeneralSettings] = useState({
        site_name: 'NoteurGoals',
        site_description: 'A comprehensive goal and note management system',
        site_url: 'https://noteurgoals.com',
        admin_email: 'admin@noteurgoals.com',
        timezone: 'Asia/Ho_Chi_Minh',
        language: 'vi',
        maintenance_mode: false
    });

    const [domainSettings, setDomainSettings] = useState({
        main_domain: 'noteurgoals.com',
        api_domain: 'api.noteurgoals.com',
        cdn_domain: 'cdn.noteurgoals.com',
        custom_domains: [
            { domain: 'goals.yourcompany.com', active: true },
            { domain: 'productivity.example.com', active: false }
        ],
        ssl_enabled: true,
        force_https: true,
        www_redirect: true
    });

    const [themeSettings, setThemeSettings] = useState({
        current_theme: 'default',
        dark_mode_enabled: true,
        custom_colors: {
            primary: '#3B82F6',
            secondary: '#8B5CF6',
            success: '#10B981',
            warning: '#F59E0B',
            danger: '#EF4444',
            background: '#F8FAFC',
            surface: '#FFFFFF',
            text_primary: '#1F2937',
            text_secondary: '#6B7280'
        },
        fonts: {
            heading: 'Inter',
            body: 'Inter',
            mono: 'JetBrains Mono'
        },
        layout: {
            sidebar_width: 'normal', // compact, normal, wide
            header_style: 'modern', // classic, modern, minimal
            card_style: 'elevated', // flat, elevated, outlined
            border_radius: 'medium' // small, medium, large, rounded
        },
        animations_enabled: true,
        custom_css: ''
    });

    const [emailSettings, setEmailSettings] = useState({
        smtp_host: 'smtp.gmail.com',
        smtp_port: '587',
        smtp_username: '',
        smtp_password: '',
        from_email: 'noreply@noteurgoals.com',
        from_name: 'NoteurGoals'
    });

    const [notificationSettings, setNotificationSettings] = useState({
        email_notifications: true,
        push_notifications: true,
        reminder_notifications: true,
        goal_progress_notifications: true,
        friend_update_notifications: true,
        ai_suggestion_notifications: true
    });

    const [securitySettings, setSecuritySettings] = useState({
        two_factor_auth: false,
        password_min_length: 8,
        password_require_uppercase: true,
        password_require_lowercase: true,
        password_require_numbers: true,
        password_require_symbols: false,
        session_timeout: 1440, // minutes
        max_login_attempts: 5
    });

    const [newAdmin, setNewAdmin] = useState({
        name: '',
        email: '',
        password: '',
        role: 'admin'
    });

    const [showNewAdminForm, setShowNewAdminForm] = useState(false);

    const handleGeneralSettingsSubmit = (e) => {
        e.preventDefault();
        // Handle general settings update
        console.log('Updating general settings:', generalSettings);
    };

    const handleEmailSettingsSubmit = (e) => {
        e.preventDefault();
        // Handle email settings update
        console.log('Updating email settings:', emailSettings);
    };

    const handleNotificationSettingsSubmit = (e) => {
        e.preventDefault();
        // Handle notification settings update
        console.log('Updating notification settings:', notificationSettings);
    };

    const handleSecuritySettingsSubmit = (e) => {
        e.preventDefault();
        // Handle security settings update
        console.log('Updating security settings:', securitySettings);
    };

    const handleDomainSettingsSubmit = (e) => {
        e.preventDefault();
        // Handle domain settings update
        console.log('Updating domain settings:', domainSettings);
    };

    const handleThemeSettingsSubmit = (e) => {
        e.preventDefault();
        // Handle theme settings update
        console.log('Updating theme settings:', themeSettings);
    };

    const addCustomDomain = () => {
        setDomainSettings(prev => ({
            ...prev,
            custom_domains: [...prev.custom_domains, { domain: '', active: false }]
        }));
    };

    const removeCustomDomain = (index) => {
        setDomainSettings(prev => ({
            ...prev,
            custom_domains: prev.custom_domains.filter((_, i) => i !== index)
        }));
    };

    const updateCustomDomain = (index, field, value) => {
        setDomainSettings(prev => ({
            ...prev,
            custom_domains: prev.custom_domains.map((domain, i) => 
                i === index ? { ...domain, [field]: value } : domain
            )
        }));
    };

    const handleCreateAdmin = (e) => {
        e.preventDefault();
        router.post(route('admin.create-admin'), newAdmin, {
            onSuccess: () => {
                setNewAdmin({ name: '', email: '', password: '', role: 'admin' });
                setShowNewAdminForm(false);
            }
        });
    };

    const SettingCard = ({ title, description, children }) => (
        <div className="bg-white shadow-sm sm:rounded-lg mb-6">
            <div className="px-4 py-5 sm:p-6">
                <div className="mb-4">
                    <h3 className="text-lg font-medium text-gray-900">{title}</h3>
                    {description && <p className="text-sm text-gray-500 mt-1">{description}</p>}
                </div>
                {children}
            </div>
        </div>
    );

    const InputField = ({ label, type = 'text', value, onChange, placeholder, required = false }) => (
        <div className="mb-4">
            <label className="block text-sm font-medium text-gray-700 mb-2">
                {label} {required && <span className="text-red-500">*</span>}
            </label>
            <input
                type={type}
                value={value}
                onChange={onChange}
                placeholder={placeholder}
                required={required}
                className="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
            />
        </div>
    );

    const SelectField = ({ label, value, onChange, options, required = false }) => (
        <div className="mb-4">
            <label className="block text-sm font-medium text-gray-700 mb-2">
                {label} {required && <span className="text-red-500">*</span>}
            </label>
            <select
                value={value}
                onChange={onChange}
                required={required}
                className="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
            >
                {options.map((option) => (
                    <option key={option.value} value={option.value}>
                        {option.label}
                    </option>
                ))}
            </select>
        </div>
    );

    const ToggleField = ({ label, description, checked, onChange }) => (
        <div className="flex items-start justify-between py-3">
            <div className="flex-1">
                <div className="text-sm font-medium text-gray-700">{label}</div>
                {description && <div className="text-sm text-gray-500">{description}</div>}
            </div>
            <div className="ml-4">
                <button
                    type="button"
                    onClick={() => onChange(!checked)}
                    className={`relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 ${
                        checked ? 'bg-blue-600' : 'bg-gray-200'
                    }`}
                >
                    <span
                        className={`pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out ${
                            checked ? 'translate-x-5' : 'translate-x-0'
                        }`}
                    />
                </button>
            </div>
        </div>
    );

    return (
        <AdminLayout
            user={auth.user}
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Settings
                </h2>
            }
        >
            <Head title="Settings - Admin" />

            <div className="py-12">
                <div className="mx-auto max-w-4xl sm:px-6 lg:px-8">
                    {/* Header */}
                    <div className="mb-6">
                        <h2 className="text-2xl font-bold leading-7 text-gray-900">System Settings</h2>
                        <p className="mt-1 text-sm text-gray-500">
                            Configure your application settings and preferences.
                        </p>
                    </div>

                    {/* General Settings */}
                    <SettingCard
                        title="General Settings"
                        description="Basic application configuration and site information."
                    >
                        <form onSubmit={handleGeneralSettingsSubmit}>
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <InputField
                                    label="Site Name"
                                    value={generalSettings.site_name}
                                    onChange={(e) => setGeneralSettings({...generalSettings, site_name: e.target.value})}
                                    required
                                />
                                <InputField
                                    label="Admin Email"
                                    type="email"
                                    value={generalSettings.admin_email}
                                    onChange={(e) => setGeneralSettings({...generalSettings, admin_email: e.target.value})}
                                    required
                                />
                                <InputField
                                    label="Site URL"
                                    type="url"
                                    value={generalSettings.site_url}
                                    onChange={(e) => setGeneralSettings({...generalSettings, site_url: e.target.value})}
                                />
                                <SelectField
                                    label="Timezone"
                                    value={generalSettings.timezone}
                                    onChange={(e) => setGeneralSettings({...generalSettings, timezone: e.target.value})}
                                    options={[
                                        { value: 'Asia/Ho_Chi_Minh', label: 'Asia/Ho Chi Minh (UTC+7)' },
                                        { value: 'UTC', label: 'UTC (UTC+0)' },
                                        { value: 'America/New_York', label: 'America/New York (UTC-5)' },
                                        { value: 'Europe/London', label: 'Europe/London (UTC+0)' }
                                    ]}
                                />
                            </div>
                            <div className="mb-4">
                                <label className="block text-sm font-medium text-gray-700 mb-2">
                                    Site Description
                                </label>
                                <textarea
                                    value={generalSettings.site_description}
                                    onChange={(e) => setGeneralSettings({...generalSettings, site_description: e.target.value})}
                                    rows={3}
                                    className="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                />
                            </div>
                            <div className="border-t border-gray-200 pt-4">
                                <ToggleField
                                    label="Maintenance Mode"
                                    description="Enable maintenance mode to temporarily disable access to the application."
                                    checked={generalSettings.maintenance_mode}
                                    onChange={(value) => setGeneralSettings({...generalSettings, maintenance_mode: value})}
                                />
                            </div>
                            <div className="flex justify-end">
                                <button
                                    type="submit"
                                    className="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:ring-2 focus:ring-blue-500"
                                >
                                    Save Changes
                                </button>
                            </div>
                        </form>
                    </SettingCard>

                    {/* Email Settings */}
                    <SettingCard
                        title="Email Settings"
                        description="Configure SMTP settings for sending emails."
                    >
                        <form onSubmit={handleEmailSettingsSubmit}>
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <InputField
                                    label="SMTP Host"
                                    value={emailSettings.smtp_host}
                                    onChange={(e) => setEmailSettings({...emailSettings, smtp_host: e.target.value})}
                                    required
                                />
                                <InputField
                                    label="SMTP Port"
                                    type="number"
                                    value={emailSettings.smtp_port}
                                    onChange={(e) => setEmailSettings({...emailSettings, smtp_port: e.target.value})}
                                    required
                                />
                                <InputField
                                    label="SMTP Username"
                                    value={emailSettings.smtp_username}
                                    onChange={(e) => setEmailSettings({...emailSettings, smtp_username: e.target.value})}
                                />
                                <InputField
                                    label="SMTP Password"
                                    type="password"
                                    value={emailSettings.smtp_password}
                                    onChange={(e) => setEmailSettings({...emailSettings, smtp_password: e.target.value})}
                                />
                                <InputField
                                    label="From Email"
                                    type="email"
                                    value={emailSettings.from_email}
                                    onChange={(e) => setEmailSettings({...emailSettings, from_email: e.target.value})}
                                    required
                                />
                                <InputField
                                    label="From Name"
                                    value={emailSettings.from_name}
                                    onChange={(e) => setEmailSettings({...emailSettings, from_name: e.target.value})}
                                    required
                                />
                            </div>
                            <div className="flex justify-end">
                                <button
                                    type="submit"
                                    className="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:ring-2 focus:ring-blue-500"
                                >
                                    Save Email Settings
                                </button>
                            </div>
                        </form>
                    </SettingCard>

                    {/* Notification Settings */}
                    <SettingCard
                        title="Notification Settings"
                        description="Configure which types of notifications are enabled."
                    >
                        <form onSubmit={handleNotificationSettingsSubmit}>
                            <div className="space-y-4">
                                <ToggleField
                                    label="Email Notifications"
                                    description="Send notifications via email to users."
                                    checked={notificationSettings.email_notifications}
                                    onChange={(value) => setNotificationSettings({...notificationSettings, email_notifications: value})}
                                />
                                <ToggleField
                                    label="Push Notifications"
                                    description="Send push notifications to mobile devices."
                                    checked={notificationSettings.push_notifications}
                                    onChange={(value) => setNotificationSettings({...notificationSettings, push_notifications: value})}
                                />
                                <ToggleField
                                    label="Reminder Notifications"
                                    description="Send reminder notifications for goals and tasks."
                                    checked={notificationSettings.reminder_notifications}
                                    onChange={(value) => setNotificationSettings({...notificationSettings, reminder_notifications: value})}
                                />
                                <ToggleField
                                    label="Goal Progress Notifications"
                                    description="Send notifications when goals are updated or completed."
                                    checked={notificationSettings.goal_progress_notifications}
                                    onChange={(value) => setNotificationSettings({...notificationSettings, goal_progress_notifications: value})}
                                />
                                <ToggleField
                                    label="Friend Update Notifications"
                                    description="Send notifications about friend activities."
                                    checked={notificationSettings.friend_update_notifications}
                                    onChange={(value) => setNotificationSettings({...notificationSettings, friend_update_notifications: value})}
                                />
                                <ToggleField
                                    label="AI Suggestion Notifications"
                                    description="Send AI-generated suggestions and recommendations."
                                    checked={notificationSettings.ai_suggestion_notifications}
                                    onChange={(value) => setNotificationSettings({...notificationSettings, ai_suggestion_notifications: value})}
                                />
                            </div>
                            <div className="flex justify-end mt-6">
                                <button
                                    type="submit"
                                    className="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:ring-2 focus:ring-blue-500"
                                >
                                    Save Notification Settings
                                </button>
                            </div>
                        </form>
                    </SettingCard>

                    {/* Security Settings */}
                    <SettingCard
                        title="Security Settings"
                        description="Configure security and authentication settings."
                    >
                        <form onSubmit={handleSecuritySettingsSubmit}>
                            <div className="space-y-4">
                                <ToggleField
                                    label="Two-Factor Authentication"
                                    description="Require two-factor authentication for admin accounts."
                                    checked={securitySettings.two_factor_auth}
                                    onChange={(value) => setSecuritySettings({...securitySettings, two_factor_auth: value})}
                                />
                                
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <InputField
                                        label="Minimum Password Length"
                                        type="number"
                                        value={securitySettings.password_min_length}
                                        onChange={(e) => setSecuritySettings({...securitySettings, password_min_length: parseInt(e.target.value)})}
                                        min="6"
                                        max="20"
                                    />
                                    <InputField
                                        label="Session Timeout (minutes)"
                                        type="number"
                                        value={securitySettings.session_timeout}
                                        onChange={(e) => setSecuritySettings({...securitySettings, session_timeout: parseInt(e.target.value)})}
                                        min="30"
                                        max="10080"
                                    />
                                    <InputField
                                        label="Max Login Attempts"
                                        type="number"
                                        value={securitySettings.max_login_attempts}
                                        onChange={(e) => setSecuritySettings({...securitySettings, max_login_attempts: parseInt(e.target.value)})}
                                        min="3"
                                        max="10"
                                    />
                                </div>

                                <div className="border-t border-gray-200 pt-4">
                                    <h4 className="text-sm font-medium text-gray-900 mb-4">Password Requirements</h4>
                                    <div className="space-y-3">
                                        <ToggleField
                                            label="Require Uppercase Letters"
                                            checked={securitySettings.password_require_uppercase}
                                            onChange={(value) => setSecuritySettings({...securitySettings, password_require_uppercase: value})}
                                        />
                                        <ToggleField
                                            label="Require Lowercase Letters"
                                            checked={securitySettings.password_require_lowercase}
                                            onChange={(value) => setSecuritySettings({...securitySettings, password_require_lowercase: value})}
                                        />
                                        <ToggleField
                                            label="Require Numbers"
                                            checked={securitySettings.password_require_numbers}
                                            onChange={(value) => setSecuritySettings({...securitySettings, password_require_numbers: value})}
                                        />
                                        <ToggleField
                                            label="Require Symbols"
                                            checked={securitySettings.password_require_symbols}
                                            onChange={(value) => setSecuritySettings({...securitySettings, password_require_symbols: value})}
                                        />
                                    </div>
                                </div>
                            </div>
                            <div className="flex justify-end mt-6">
                                <button
                                    type="submit"
                                    className="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:ring-2 focus:ring-blue-500"
                                >
                                    Save Security Settings
                                </button>
                            </div>
                        </form>
                    </SettingCard>

                    {/* Domain Management */}
                    <SettingCard
                        title="Domain Management"
                        description="Configure domains, SSL, and custom domain mappings."
                    >
                        <form onSubmit={handleDomainSettingsSubmit}>
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <InputField
                                    label="Main Domain"
                                    value={domainSettings.main_domain}
                                    onChange={(e) => setDomainSettings({...domainSettings, main_domain: e.target.value})}
                                    placeholder="example.com"
                                    required
                                />
                                <InputField
                                    label="API Domain"
                                    value={domainSettings.api_domain}
                                    onChange={(e) => setDomainSettings({...domainSettings, api_domain: e.target.value})}
                                    placeholder="api.example.com"
                                />
                                <InputField
                                    label="CDN Domain"
                                    value={domainSettings.cdn_domain}
                                    onChange={(e) => setDomainSettings({...domainSettings, cdn_domain: e.target.value})}
                                    placeholder="cdn.example.com"
                                />
                            </div>

                            <div className="mt-6">
                                <h4 className="text-sm font-medium text-gray-900 mb-4">SSL & Security Settings</h4>
                                <div className="space-y-3">
                                    <ToggleField
                                        label="SSL Enabled"
                                        description="Enable SSL/TLS encryption for all domains"
                                        checked={domainSettings.ssl_enabled}
                                        onChange={(value) => setDomainSettings({...domainSettings, ssl_enabled: value})}
                                    />
                                    <ToggleField
                                        label="Force HTTPS"
                                        description="Automatically redirect HTTP traffic to HTTPS"
                                        checked={domainSettings.force_https}
                                        onChange={(value) => setDomainSettings({...domainSettings, force_https: value})}
                                    />
                                    <ToggleField
                                        label="WWW Redirect"
                                        description="Redirect www subdomain to main domain"
                                        checked={domainSettings.www_redirect}
                                        onChange={(value) => setDomainSettings({...domainSettings, www_redirect: value})}
                                    />
                                </div>
                            </div>

                            <div className="mt-6">
                                <div className="flex items-center justify-between mb-4">
                                    <h4 className="text-sm font-medium text-gray-900">Custom Domains</h4>
                                    <button
                                        type="button"
                                        onClick={addCustomDomain}
                                        className="px-3 py-1 text-sm bg-blue-600 text-white rounded-md hover:bg-blue-700"
                                    >
                                        Add Domain
                                    </button>
                                </div>
                                <div className="space-y-3">
                                    {domainSettings.custom_domains.map((customDomain, index) => (
                                        <div key={index} className="flex items-center space-x-3 p-3 border border-gray-200 rounded-lg">
                                            <input
                                                type="text"
                                                value={customDomain.domain}
                                                onChange={(e) => updateCustomDomain(index, 'domain', e.target.value)}
                                                placeholder="custom.example.com"
                                                className="flex-1 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                            />
                                            <button
                                                type="button"
                                                onClick={() => updateCustomDomain(index, 'active', !customDomain.active)}
                                                className={`px-3 py-1 text-sm rounded-md ${
                                                    customDomain.active 
                                                        ? 'bg-green-100 text-green-800' 
                                                        : 'bg-gray-100 text-gray-800'
                                                }`}
                                            >
                                                {customDomain.active ? 'Active' : 'Inactive'}
                                            </button>
                                            <button
                                                type="button"
                                                onClick={() => removeCustomDomain(index)}
                                                className="px-3 py-1 text-sm bg-red-100 text-red-800 rounded-md hover:bg-red-200"
                                            >
                                                Remove
                                            </button>
                                        </div>
                                    ))}
                                </div>
                            </div>

                            <div className="flex justify-end mt-6">
                                <button
                                    type="submit"
                                    className="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:ring-2 focus:ring-blue-500"
                                >
                                    Save Domain Settings
                                </button>
                            </div>
                        </form>
                    </SettingCard>

                    {/* Theme Customization */}
                    <SettingCard
                        title="Theme & Appearance"
                        description="Customize the look and feel of your admin panel and user interface."
                    >
                        <form onSubmit={handleThemeSettingsSubmit}>
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                {/* Theme Selection */}
                                <div>
                                    <SelectField
                                        label="Current Theme"
                                        value={themeSettings.current_theme}
                                        onChange={(e) => setThemeSettings({...themeSettings, current_theme: e.target.value})}
                                        options={[
                                            { value: 'default', label: 'Default Theme' },
                                            { value: 'dark', label: 'Dark Theme' },
                                            { value: 'minimal', label: 'Minimal Theme' },
                                            { value: 'corporate', label: 'Corporate Theme' },
                                            { value: 'custom', label: 'Custom Theme' }
                                        ]}
                                    />
                                </div>

                                {/* Layout Settings */}
                                <div>
                                    <SelectField
                                        label="Sidebar Width"
                                        value={themeSettings.layout.sidebar_width}
                                        onChange={(e) => setThemeSettings({
                                            ...themeSettings, 
                                            layout: {...themeSettings.layout, sidebar_width: e.target.value}
                                        })}
                                        options={[
                                            { value: 'compact', label: 'Compact' },
                                            { value: 'normal', label: 'Normal' },
                                            { value: 'wide', label: 'Wide' }
                                        ]}
                                    />
                                </div>

                                <div>
                                    <SelectField
                                        label="Header Style"
                                        value={themeSettings.layout.header_style}
                                        onChange={(e) => setThemeSettings({
                                            ...themeSettings, 
                                            layout: {...themeSettings.layout, header_style: e.target.value}
                                        })}
                                        options={[
                                            { value: 'classic', label: 'Classic' },
                                            { value: 'modern', label: 'Modern' },
                                            { value: 'minimal', label: 'Minimal' }
                                        ]}
                                    />
                                </div>

                                <div>
                                    <SelectField
                                        label="Card Style"
                                        value={themeSettings.layout.card_style}
                                        onChange={(e) => setThemeSettings({
                                            ...themeSettings, 
                                            layout: {...themeSettings.layout, card_style: e.target.value}
                                        })}
                                        options={[
                                            { value: 'flat', label: 'Flat' },
                                            { value: 'elevated', label: 'Elevated' },
                                            { value: 'outlined', label: 'Outlined' }
                                        ]}
                                    />
                                </div>
                            </div>

                            {/* Color Customization */}
                            <div className="mt-6">
                                <h4 className="text-sm font-medium text-gray-900 mb-4">Color Palette</h4>
                                <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
                                    {Object.entries(themeSettings.custom_colors).map(([colorKey, colorValue]) => (
                                        <div key={colorKey}>
                                            <label className="block text-xs font-medium text-gray-700 mb-1 capitalize">
                                                {colorKey.replace('_', ' ')}
                                            </label>
                                            <div className="flex items-center space-x-2">
                                                <input
                                                    type="color"
                                                    value={colorValue}
                                                    onChange={(e) => setThemeSettings({
                                                        ...themeSettings,
                                                        custom_colors: {
                                                            ...themeSettings.custom_colors,
                                                            [colorKey]: e.target.value
                                                        }
                                                    })}
                                                    className="h-8 w-16 rounded border border-gray-300"
                                                />
                                                <input
                                                    type="text"
                                                    value={colorValue}
                                                    onChange={(e) => setThemeSettings({
                                                        ...themeSettings,
                                                        custom_colors: {
                                                            ...themeSettings.custom_colors,
                                                            [colorKey]: e.target.value
                                                        }
                                                    })}
                                                    className="flex-1 text-xs rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                                />
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            </div>

                            {/* Font Settings */}
                            <div className="mt-6">
                                <h4 className="text-sm font-medium text-gray-900 mb-4">Typography</h4>
                                <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <SelectField
                                        label="Heading Font"
                                        value={themeSettings.fonts.heading}
                                        onChange={(e) => setThemeSettings({
                                            ...themeSettings,
                                            fonts: {...themeSettings.fonts, heading: e.target.value}
                                        })}
                                        options={[
                                            { value: 'Inter', label: 'Inter' },
                                            { value: 'Roboto', label: 'Roboto' },
                                            { value: 'Poppins', label: 'Poppins' },
                                            { value: 'Montserrat', label: 'Montserrat' },
                                            { value: 'Open Sans', label: 'Open Sans' }
                                        ]}
                                    />
                                    <SelectField
                                        label="Body Font"
                                        value={themeSettings.fonts.body}
                                        onChange={(e) => setThemeSettings({
                                            ...themeSettings,
                                            fonts: {...themeSettings.fonts, body: e.target.value}
                                        })}
                                        options={[
                                            { value: 'Inter', label: 'Inter' },
                                            { value: 'Roboto', label: 'Roboto' },
                                            { value: 'Poppins', label: 'Poppins' },
                                            { value: 'Montserrat', label: 'Montserrat' },
                                            { value: 'Open Sans', label: 'Open Sans' }
                                        ]}
                                    />
                                    <SelectField
                                        label="Monospace Font"
                                        value={themeSettings.fonts.mono}
                                        onChange={(e) => setThemeSettings({
                                            ...themeSettings,
                                            fonts: {...themeSettings.fonts, mono: e.target.value}
                                        })}
                                        options={[
                                            { value: 'JetBrains Mono', label: 'JetBrains Mono' },
                                            { value: 'Fira Code', label: 'Fira Code' },
                                            { value: 'Source Code Pro', label: 'Source Code Pro' },
                                            { value: 'Monaco', label: 'Monaco' }
                                        ]}
                                    />
                                </div>
                            </div>

                            {/* Advanced Options */}
                            <div className="mt-6">
                                <h4 className="text-sm font-medium text-gray-900 mb-4">Advanced Options</h4>
                                <div className="space-y-4">
                                    <ToggleField
                                        label="Dark Mode Support"
                                        description="Enable dark mode toggle for users"
                                        checked={themeSettings.dark_mode_enabled}
                                        onChange={(value) => setThemeSettings({...themeSettings, dark_mode_enabled: value})}
                                    />
                                    <ToggleField
                                        label="Animations"
                                        description="Enable smooth animations and transitions"
                                        checked={themeSettings.animations_enabled}
                                        onChange={(value) => setThemeSettings({...themeSettings, animations_enabled: value})}
                                    />
                                </div>
                            </div>

                            {/* Custom CSS */}
                            <div className="mt-6">
                                <label className="block text-sm font-medium text-gray-700 mb-2">
                                    Custom CSS
                                </label>
                                <textarea
                                    value={themeSettings.custom_css}
                                    onChange={(e) => setThemeSettings({...themeSettings, custom_css: e.target.value})}
                                    rows={6}
                                    placeholder="/* Add your custom CSS here */"
                                    className="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm font-mono"
                                />
                                <p className="text-xs text-gray-500 mt-1">
                                    Custom CSS will be applied to all pages. Use with caution.
                                </p>
                            </div>

                            <div className="flex justify-end mt-6">
                                <button
                                    type="submit"
                                    className="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:ring-2 focus:ring-blue-500"
                                >
                                    Save Theme Settings
                                </button>
                            </div>
                        </form>
                    </SettingCard>

                    {/* Admin Management */}
                    <SettingCard
                        title="Admin Management"
                        description="Manage administrator accounts and permissions."
                    >
                        <div className="mb-4">
                            <button
                                onClick={() => setShowNewAdminForm(!showNewAdminForm)}
                                className="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:ring-2 focus:ring-green-500"
                            >
                                {showNewAdminForm ? 'Cancel' : 'Add New Admin'}
                            </button>
                        </div>

                        {showNewAdminForm && (
                            <div className="border border-gray-200 rounded-lg p-4 mb-6">
                                <h4 className="text-lg font-medium text-gray-900 mb-4">Create New Admin</h4>
                                <form onSubmit={handleCreateAdmin}>
                                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <InputField
                                            label="Name"
                                            value={newAdmin.name}
                                            onChange={(e) => setNewAdmin({...newAdmin, name: e.target.value})}
                                            required
                                        />
                                        <InputField
                                            label="Email"
                                            type="email"
                                            value={newAdmin.email}
                                            onChange={(e) => setNewAdmin({...newAdmin, email: e.target.value})}
                                            required
                                        />
                                        <InputField
                                            label="Password"
                                            type="password"
                                            value={newAdmin.password}
                                            onChange={(e) => setNewAdmin({...newAdmin, password: e.target.value})}
                                            required
                                        />
                                        <SelectField
                                            label="Role"
                                            value={newAdmin.role}
                                            onChange={(e) => setNewAdmin({...newAdmin, role: e.target.value})}
                                            options={[
                                                { value: 'admin', label: 'Administrator' },
                                                { value: 'super_admin', label: 'Super Administrator' },
                                                { value: 'moderator', label: 'Moderator' }
                                            ]}
                                            required
                                        />
                                    </div>
                                    <div className="flex justify-end">
                                        <button
                                            type="submit"
                                            className="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:ring-2 focus:ring-blue-500"
                                        >
                                            Create Admin
                                        </button>
                                    </div>
                                </form>
                            </div>
                        )}

                        <div className="text-sm text-gray-600">
                            <p>Current admin: <strong>{auth.user.name}</strong> ({auth.user.email})</p>
                            <p className="mt-1">For security reasons, admin management features are limited in this demo.</p>
                        </div>
                    </SettingCard>

                    {/* System Information */}
                    <SettingCard
                        title="System Information"
                        description="View current system status and information."
                    >
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h4 className="text-sm font-medium text-gray-900 mb-3">Application</h4>
                                <dl className="space-y-2">
                                    <div className="flex justify-between text-sm">
                                        <dt className="text-gray-500">Version</dt>
                                        <dd className="text-gray-900">v1.0.0</dd>
                                    </div>
                                    <div className="flex justify-between text-sm">
                                        <dt className="text-gray-500">Environment</dt>
                                        <dd className="text-gray-900">Production</dd>
                                    </div>
                                    <div className="flex justify-between text-sm">
                                        <dt className="text-gray-500">Laravel Version</dt>
                                        <dd className="text-gray-900">11.x</dd>
                                    </div>
                                    <div className="flex justify-between text-sm">
                                        <dt className="text-gray-500">PHP Version</dt>
                                        <dd className="text-gray-900">8.3.x</dd>
                                    </div>
                                </dl>
                            </div>
                            <div>
                                <h4 className="text-sm font-medium text-gray-900 mb-3">System</h4>
                                <dl className="space-y-2">
                                    <div className="flex justify-between text-sm">
                                        <dt className="text-gray-500">Server Time</dt>
                                        <dd className="text-gray-900">{new Date().toLocaleString('vi-VN')}</dd>
                                    </div>
                                    <div className="flex justify-between text-sm">
                                        <dt className="text-gray-500">Database</dt>
                                        <dd className="text-gray-900">MySQL</dd>
                                    </div>
                                    <div className="flex justify-between text-sm">
                                        <dt className="text-gray-500">Cache</dt>
                                        <dd className="text-gray-900">Redis</dd>
                                    </div>
                                    <div className="flex justify-between text-sm">
                                        <dt className="text-gray-500">Queue</dt>
                                        <dd className="text-gray-900">Redis</dd>
                                    </div>
                                </dl>
                            </div>
                        </div>
                    </SettingCard>
                </div>
            </div>
        </AdminLayout>
    );
}