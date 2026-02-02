{{-- <style>

    :root{
        /* Softer dark theme (less harsh than previous) */
        --bg: #1f2937; /* slate-800 — softer background */
        --card: #0f1724; /* card slightly lighter than black */
        --muted: #9aa9bd; /* subtle muted text */
        --accent: #7c3aed; /* soft violet accent */
        --accent-contrast: #071124;
        --input: #111827; /* input surfaces slightly lighter */
        --glass: rgba(255,255,255,0.025);
        --radius: 12px;
        --container-gap: 20px;


         --primary-color: {{ config('themes.primaryColor') ?: '#2563eb' }};
        --secondary-color: {{ config('themes.secondaryColor') ?: '#1f2937' }};
        --green-color: {{ config('themes.greenColor') ?: '#10b981' }};
        --text-primary-color: {{ config('themes.textPrimaryColor') ?: '#ffffff' }};
        --text-secondary-color: {{ config('themes.textSecondaryColor') ?: '#9ca3af' }};
        --box-background-color: {{ config('themes.boxBackgroundColor') ?: '#ffffff' }};
        --table-background-color: {{ config('themes.tableBackGroundColor') ?: '#f9fafb' }};
        --background-image: {{ config('themes.backgroundImage') ?: 'none' }};
        --brand_background-image: url({{ getImagePath(config('themes.brandBackgroundImage')) ?: '' }});
        --second-alpha: rgba(31, 41, 55, 0.1);
        --primary-hover-alpha: rgba(37, 99, 235, 0.1);
        --scroll-second-color: rgba(255, 255, 255, 0.8);
        --scroll-first-color: rgba(37, 99, 235, 0.2);
    }

    body {
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 0;
        background-color: #121212;
        color: white;
        display: flex;
    }

    /* Allow scrolling when page is zoomed */
    html, body {
        overflow: auto;
    }
 .settings-sidebar{
        width: 280px;
        background: linear-gradient(180deg, rgba(255,138,0,0.12), rgba(255,138,0,0.06));
        color: #071124;
        border-radius: var(--radius);
        padding:18px;
        box-shadow: 0 6px 30px rgba(2,6,23,0.6);
        backdrop-filter: blur(6px);
        flex-shrink:0;
        align-self: flex-start;
    }

    .settings-sidebar h2{font-size:18px;margin:0 0 12px;cursor:pointer;color:var(--accent-contrast);display:flex;align-items:center;justify-content:space-between}
    /* القائمة الجانبية */
    /* .settings-sidebar {
        width: 250px;
        background: white;
        min-height: 400px;
        align-self: flex-start;
        padding: 20px;
        box-shadow: 2px 0 10px rgba(0, 0, 0, 0.5);
    } */

    /* .settings-sidebar h2 {
        text-align: center;
        color: var(--primary-color);
    } */

    .settings-menu button {
        display: block;
        width: 100%;
        text-align: right;
        padding: 15px;
        color: white;
        border: none;
        margin-bottom: 5px;
        cursor: pointer;
        font-size: 16px;
    }

    .settings-menu button:hover {
        background: var(--primary-color);
    }

    /* محتوى الصفحة */
    .settings-content {
        flex-grow: 1;
        padding: 20px;
    }

    .settings-section {
        display: none;
    }

    .active {
        display: block;
    }

    /* تنسيق النماذج */
    form {
       

         background: linear-gradient(180deg, rgba(255,138,0,0.12), rgba(255,138,0,0.06));
        color: #071124;
        border-radius: var(--radius);
        padding:18px;
        box-shadow: 0 6px 30px rgba(2,6,23,0.6);
        backdrop-filter: blur(6px);
    }
    h3 {
    color:var(--text-secondary-color);
}

    label {
        display: var(--text-secondary-color);
        margin: 10px 0 5px;
    }

    input,
    select {
        width: 100%;
        padding: 10px;
        margin-bottom: 15px;
        background: #333;
        border: 1px solid #444;
        color: white;
    }

    button {
        padding: 10px;
        border: none;
        cursor: pointer;
        font-weight: bold;
    }

    .all-page {
        display: inline-flex;
        /* allow the container to grow beyond viewport so browser shows scrollbars */
        /* min-width: max-content; */
    }

    .wrapper {
        width: 100%;
    }

    .wrapper {
        min-width: max-content;
    }

    .settings-content {
        width: 869px;

    }

    .form {
        width: 400px;
        margin: auto;
    }

    /* تصميم النافذة */
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        padding-top: 50px;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.9);
    }

    /* الصورة داخل النافذة */
    .modal-content {
        margin: auto;
        display: block;
        width: 80%;
        max-width: 700px;
    }

    /* زر الإغلاق */
    .close {
        position: absolute;
        top: 15px;
        right: 35px;
        color: white;
        font-size: 40px;
        font-weight: bold;
        cursor: pointer;
    }

    img {
        width: 201px;
        display: block;
        height: 99px;
        margin-bottom: 20px;
    }

    button {
        width: 200px;

    }

    /* Tab styling */
    .tab-buttons {
        display: flex;
        border-bottom: 1px solid #444;
        margin-bottom: 20px;
        overflow-x: auto;
    }

    .tab-button {
        padding: 10px 20px;
        background: #333;
        border: none;
        color: white;
        cursor: pointer;
        margin-right: 5px;
        border-radius: 5px 5px 0 0;
    }

    .tab-button:hover {
        background: #555;
    }

    .tab-button.active {
        background: #ff9800;
        color: #121212;
    }

    .tab-content {
        display: none;
        padding: 20px;
        background: #222;
        border-radius: 0 5px 5px 5px;
    }

    .tab-content.active {
        display: block;
    }

    .tab-content form {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
    }

    .tab-content label {
        grid-column: 1;
    }

    .tab-content input[type="file"] {
        grid-column: 2;
    }

    .tab-content button {
        grid-column: 1 / span 2;
        justify-self: center;
    }



    /* Badge Upload Section Specific Styles */
    .badge-upload-container {
        display: grid;
        width: 200%;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
        margin-bottom: 20px;
    }

    .badge-upload-item {
        display: flex;
        flex-direction: column;
        gap: 8px;
        padding: 15px;
        background: #333;
        border-radius: 5px;
        min-height: 200px;
        /* Ensures consistent height */
    }

    .badge-upload-item label {
        font-weight: bold;
        color: #ff9800;
        margin-bottom: 5px;
    }

    .badge-upload-item input[type="file"] {
        padding: 8px;
        background: #444;
        border: 1px solid #555;
        color: white;
        width: 100%;
    }

    .badge-preview {
        margin-top: 10px;
        text-align: center;
        flex-grow: 1;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .badge-preview img {
        max-width: 100%;
        max-height: 100px;
        cursor: pointer;
        border: 2px solid #555;
        transition: transform 0.3s;
    }

    .badge-preview img:hover {
        transform: scale(1.05);
        border-color: #ff9800;
    }

    .upload-button {
        display: block;
        width: auto;
        margin: 20px auto 0;
        padding: 10px 30px;
        background: #ff9800;
        color: #121212;
        font-weight: bold;
        border-radius: 5px;
        transition: background 0.3s;
        grid-column: 1 / -1;
        /* Span full width */
    }

    .upload-button:hover {
        background: #ffab40;
    }
    .swal-wide {
        width: 900px !important;
        font-size: 25px;
    }

    /* Responsive adjustments */
    @media (max-width: 2000px) {
        .badge-upload-container {
            grid-template-columns: 1fr;
        }
    }

    .switch-container {
        display: flex;
        flex-wrap: wrap;
        justify-content: flex-start;
        margin-top: 16px;
    }

    .switch-item {
        display: flex;
        align-items: center;
        margin-bottom: 15px;
        margin-right: 20px;
    }

    .switch-label {
        margin-left: 10px;
        font-weight: 500;
    }

    .switch {
        position: relative;
        display: inline-block;
        width: 50px;
        height: 24px;
    }

    .switch input {
        display: none;
    }

    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        transition: .4s;
        border-radius: 34px;
    }

    .slider:before {
        position: absolute;
        content: "";
        height: 18px;
        width: 18px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: .4s;
        border-radius: 50%;
    }

    input:checked + .slider {
        background-color: #00e6c3;
    }

    input:checked + .slider:before {
        transform: translateX(26px);
    }

    .grid-per-pager .input, select{
        width: auto !important;
    }

    .exportPdfForm {
        background: transparent !important;
    }

    .modal-body input,
    .modal-body select {
        width: auto !important;
    }

    .close {
        right: 0 !important;
        top: 0 !important;
        font-size: 30px !important;
    }

    button.close {
        width: auto !important;
    }

    .btn-sm {
        margin: 0 !important;
    }

    /* للشاشات الصغيرة (موبايل) */
@media (max-width: 768px) {
    body {
        flex-direction: column;
    }

    .all-page {
        flex-direction: column;
        display: flex;
    }

    /* .settings-sidebar {
        /*width: 100%;*/
        /* padding: 10px;
        box-shadow: none;
        position: relative;
    } */ */

    /* .settings-sidebar h2 {
        margin-bottom: 10px;
    } */

    /*.settings-menu {*/
    /*    display: flex;*/
    /*    flex-wrap: wrap;*/
    /*    gap: 5px;*/
    /*}*/

    .settings-menu button {
        flex: 1 1 45%;
        font-size: 14px;
        padding: 10px;
        margin: 2px 0;
    }

    .settings-content {
        width: 100% !important;
        padding: 10px;
    }

    .form {
        width: 100% !important;
    }

    .badge-upload-container {
        grid-template-columns: 1fr !important;
        width: 100% !important;
    }
}

</style> --}}


<style>
    :root {
        /* Modern color palette */
        
        --bg-tertiary: #334155;
        --accent-primary: #7c3aed;
        --accent-secondary: #f59e0b;
        --text-primary: #f1f5f9;
        --text-secondary: #94a3b8;
        --text-muted: #64748b;
        --border-color: #334155;
        --success: #10b981;
        --warning: #f59e0b;
        --danger: #ef4444;
        --radius-lg: 16px;
        --radius-md: 12px;
        --radius-sm: 8px;
        --shadow-lg: 0 20px 40px rgba(0, 0, 0, 0.3);
        --shadow-md: 0 10px 20px rgba(0, 0, 0, 0.2);
        --shadow-sm: 0 4px 12px rgba(0, 0, 0, 0.1);
        --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);

          --bg-primary: {{ config('themes.primaryColor') ?: '#2563eb' }};
        --bg-secondary: {{ config('themes.secondaryColor') ?: '#1f2937' }};
        --green-color: {{ config('themes.greenColor') ?: '#10b981' }};
        --text-primary-color: {{ config('themes.textPrimaryColor') ?: '#ffffff' }};
       --text-secondary: {{ config('themes.textSecondaryColor') ?: '#9ca3af' }};
        --box-background-color: {{ config('themes.boxBackgroundColor') ?: '#ffffff' }};
        --table-background-color: {{ config('themes.tableBackGroundColor') ?: '#f9fafb' }};
        --background-image: {{ config('themes.backgroundImage') ?: 'none' }};
        --brand_background-image: url({{ getImagePath(config('themes.brandBackgroundImage')) ?: '' }});
        --second-alpha: rgba(31, 41, 55, 0.1);
        --primary-hover-alpha: rgba(37, 99, 235, 0.1);
        --scroll-second-color: rgba(255, 255, 255, 0.8);
        --scroll-first-color: rgba(37, 99, 235, 0.2);
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        color: var(--text-primary);
        line-height: 1.6;
        min-height: 100vh;
        overflow-x: hidden;
    }

    .all-page {
        display: flex;
        min-height: 100vh;
        gap: 2rem;
        padding: 2rem;
        max-width: 1400px;
        margin: 0 auto;
    }

    /* Sidebar Styling */
    .settings-sidebar {
        width: 280px;
        flex-shrink: 0;
         background: linear-gradient(180deg, rgba(255,138,0,0.12), rgba(255,138,0,0.06));

        border-radius: var(--radius-lg);
        padding: 1.5rem;
        box-shadow: var(--shadow-lg);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.05);
        position: sticky;
        top: 2rem;
        height: fit-content;
    }

    .settings-sidebar h2 {
        font-size: 1.5rem;
        font-weight: 700;
        margin-bottom: 2rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid var(--accent-primary);
        color: var(--text-primary);
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .settings-sidebar h2::before {
        content: '⚙️';
        font-size: 1.25rem;
    }

    .settings-menu {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .settings-menu button {
        background: transparent;
        border: 1px solid var(--border-color);
        color: var(--text-secondary);
        padding: 1rem 1.25rem;
        border-radius: var(--radius-md);
        text-align: left;
        font-size: 0.95rem;
        font-weight: 500;
        cursor: pointer;
        transition: var(--transition);
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .settings-menu button::before {
        content: '→';
        opacity: 0.5;
        transition: var(--transition);
    }

    .settings-menu button:hover {
        background: rgba(124, 58, 237, 0.1);
        border-color: var(--accent-primary);
        color: var(--text-primary);
        transform: translateX(4px);
    }

    .settings-menu button:hover::before {
        opacity: 1;
        transform: translateX(2px);
    }

    .settings-menu button.active {
        background: linear-gradient(135deg, var(--accent-primary), #9d4edd);
        border-color: transparent;
        color: white;
        box-shadow: 0 4px 20px rgba(124, 58, 237, 0.3);
    }

    .settings-menu button.active::before {
        content: '✓';
        opacity: 1;
    }

    /* Main Content Area */
    .settings-content {
        flex: 1;
        background: var(--bg-secondary);
        border-radius: var(--radius-lg);
        padding: 2rem;
        box-shadow: var(--shadow-lg);
        border: 1px solid rgba(255, 255, 255, 0.05);
        min-height: 600px;
    }

    .settings-section {
        display: none;
        animation: fadeIn 0.4s ease-out;
    }

    .settings-section.active {
        display: block;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Section Headers */
    .settings-section h3 {
        font-size: 1.75rem;
        font-weight: 700;
        margin-bottom: 2rem;
        color: var(--text-primary);
        position: relative;
        padding-bottom: 0.75rem;
    }

    .settings-section h3::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 60px;
        height: 4px;
        background: linear-gradient(90deg, var(--accent-primary), var(--accent-secondary));
        border-radius: 2px;
    }

    /* Form Styling */
    form {
        background: linear-gradient(180deg, rgba(255,138,0,0.12), rgba(255,138,0,0.06));;
        border-radius: var(--radius-lg);
        padding: 2rem;
        box-shadow: var(--shadow-md);
        border: 1px solid var(--border-color);
    }

    .form {
        max-width: 500px;
        margin: 0;
    }

    label {
        display: block;
        margin-bottom: 0.5rem;
        color: var(--text-secondary);
        font-weight: 500;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    input[type="text"],
    input[type="number"],
    input[type="email"],
    input[type="password"],
    select {
        width: 100%;
        padding: 1rem;
        background: var(--bg-tertiary);
        border: 2px solid var(--border-color);
        border-radius: var(--radius-md);
        color: var(--text-primary);
        font-size: 1rem;
        transition: var(--transition);
        margin-bottom: 1.5rem;
    }

    input:focus,
    select:focus {
        outline: none;
        border-color: var(--accent-primary);
        box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.2);
        background: var(--bg-secondary);
    }

    /* Buttons */
    button[type="submit"],
    button.save-btn {
        background: linear-gradient(135deg, var(--accent-primary), #9d4edd);
        color: white;
        border: none;
        padding: 1rem 2rem;
        border-radius: var(--radius-md);
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: var(--transition);
        width: auto;
        min-width: 150px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        box-shadow: 0 4px 20px rgba(124, 58, 237, 0.3);
    }

    button[type="submit"]:hover,
    button.save-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 25px rgba(124, 58, 237, 0.4);
    }

    button[type="submit"]::before,
    button.save-btn::before {
        content: '💾';
    }

    /* Remaining Diamonds Section */
    .remaining-diamonds-section {
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(16, 185, 129, 0.05));
        border: 1px solid rgba(16, 185, 129, 0.2);
        border-radius: var(--radius-lg);
        padding: 2rem;
        margin-top: 2rem;
    }

    .remaining-diamonds-section h3 {
        color: var(--success);
    }

    .remaining-diamonds-section h3::after {
        background: linear-gradient(90deg, var(--success), #34d399);
    }

    .info-box {
        background: rgba(16, 185, 129, 0.1);
        border: 1px solid rgba(16, 185, 129, 0.2);
        border-radius: var(--radius-md);
        padding: 1rem;
        margin: 1.5rem 0;
        display: flex;
        align-items: flex-start;
        gap: 1rem;
    }

    .info-box::before {
        content: 'ℹ️';
        font-size: 1.25rem;
    }

    /* Modal Styling */
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background: rgba(15, 23, 42, 0.95);
        backdrop-filter: blur(5px);
        animation: fadeIn 0.3s ease-out;
    }

    .modal-content {
        margin: auto;
        display: block;
        max-width: 90%;
        max-height: 90vh;
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-lg);
        animation: scaleIn 0.3s ease-out;
    }

    @keyframes scaleIn {
        from {
            opacity: 0;
            transform: scale(0.9);
        }
        to {
            opacity: 1;
            transform: scale(1);
        }
    }

    .close {
        position: absolute;
        top: 2rem;
        right: 2rem;
        color: white;
        font-size: 2.5rem;
        font-weight: 300;
        cursor: pointer;
        transition: var(--transition);
        width: 3rem;
        height: 3rem;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.1);
    }

    .close:hover {
        background: rgba(255, 255, 255, 0.2);
        transform: rotate(90deg);
    }

    /* Badge Upload Section */
    .badge-upload-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1.5rem;
        margin: 2rem 0;
    }

    .badge-upload-item {
        background: var(--bg-primary);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 1.5rem;
        transition: var(--transition);
    }

    .badge-upload-item:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-md);
        border-color: var(--accent-primary);
    }

    .badge-preview {
        width: 100%;
        height: 150px;
        background: var(--bg-tertiary);
        border-radius: var(--radius-md);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 1rem 0;
        overflow: hidden;
        border: 2px dashed var(--border-color);
    }

    .badge-preview img {
        max-width: 80%;
        max-height: 80%;
        object-fit: contain;
        border-radius: var(--radius-sm);
        transition: var(--transition);
    }

    .badge-preview img:hover {
        transform: scale(1.1);
    }

    /* Grid for percentage targets */
    .percentage-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
        margin: 2rem 0;
    }

    .percentage-item {
        background: linear-gradient(135deg, var(--bg-tertiary), var(--bg-secondary));
        border-radius: var(--radius-lg);
        padding: 1.5rem;
        border: 1px solid var(--border-color);
    }

    .percentage-item label {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 1rem;
    }

    .percentage-item label::before {
        content: '⭐';
        font-size: 1.2rem;
    }

    /* Responsive Design */
    @media (max-width: 1024px) {
        .all-page {
            flex-direction: column;
            padding: 1rem;
        }
        
        .settings-sidebar {
            width: 100%;
            position: static;
            margin-bottom: 1rem;
        }
        
        .settings-menu {
            flex-direction: row;
            flex-wrap: wrap;
        }
        
        .settings-menu button {
            flex: 1;
            min-width: 200px;
        }
    }

    @media (max-width: 768px) {
        .settings-content {
            padding: 1.5rem;
        }
        
        .badge-upload-container {
            grid-template-columns: 1fr;
        }
        
        .percentage-grid {
            grid-template-columns: 1fr;
        }
        
        button[type="submit"] {
            width: 100%;
        }
    }

    @media (max-width: 480px) {
        .settings-menu button {
            min-width: 100%;
        }
        
        .settings-section h3 {
            font-size: 1.5rem;
        }
    }

    /* Custom scrollbar */
    ::-webkit-scrollbar {
        width: 10px;
    }

    ::-webkit-scrollbar-track {
        background: var(--bg-primary);
    }

    ::-webkit-scrollbar-thumb {
        background: var(--accent-primary);
        border-radius: 5px;
    }

    ::-webkit-scrollbar-thumb:hover {
        background: #9d4edd;
    }

    /* Loading animation for form submission */
    .loading {
        position: relative;
        pointer-events: none;
        opacity: 0.7;
    }

    .loading::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 20px;
        height: 20px;
        border: 2px solid white;
        border-top-color: transparent;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    /* Toast notifications styling */
    .swal-wide {
        font-family: 'Inter', sans-serif !important;
        background: var(--bg-secondary) !important;
        color: var(--text-primary) !important;
        border: 1px solid var(--border-color) !important;
        border-radius: var(--radius-lg) !important;
    }

    /* Switch toggle styling */
    .switch-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1rem;
        margin: 2rem 0;
    }

    .switch-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: var(--bg-primary);
        padding: 1rem 1.5rem;
        border-radius: var(--radius-md);
        border: 1px solid var(--border-color);
        transition: var(--transition);
    }

    .switch-item:hover {
        border-color: var(--accent-primary);
        transform: translateX(4px);
    }

    /* Grid tables styling */
    .grid-per-pager {
        background: var(--bg-primary);
        border-radius: var(--radius-lg);
        overflow: hidden;
        box-shadow: var(--shadow-md);
    }

    .grid-per-pager table {
        width: 100%;
        border-collapse: collapse;
    }

    .grid-per-pager th {
        background: var(--accent-primary);
        color: white;
        padding: 1rem;
        text-align: left;
        font-weight: 600;
    }

    .grid-per-pager td {
        padding: 1rem;
        border-bottom: 1px solid var(--border-color);
        color: var(--text-secondary);
    }

    .grid-per-pager tr:hover {
        background: rgba(124, 58, 237, 0.1);
    }

    /* Alert styling */
    .alert {
        padding: 1rem 1.5rem;
        border-radius: var(--radius-md);
        margin: 1rem 0;
        display: flex;
        align-items: center;
        gap: 1rem;
        animation: slideIn 0.3s ease-out;
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateX(-20px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    .alert-danger {
        background: linear-gradient(135deg, rgba(239, 68, 68, 0.15), rgba(239, 68, 68, 0.05));
        border: 1px solid rgba(239, 68, 68, 0.3);
        color: #fca5a5;
    }

    .alert-danger::before {
        content: '⚠️';
    }

    .alert-info {
        background: linear-gradient(135deg, rgba(59, 130, 246, 0.15), rgba(59, 130, 246, 0.05));
        border: 1px solid rgba(59, 130, 246, 0.3);
        color: #93c5fd;
    }

    .alert-info::before {
        content: 'ℹ️';
    }

    .alert-success {
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.15), rgba(16, 185, 129, 0.05));
        border: 1px solid rgba(16, 185, 129, 0.3);
        color: #6ee7b7;
    }

    .alert-success::before {
        content: '✅';
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>

<body>
    <div class="all-page">
        <div class="settings-sidebar">
            <h2 onclick="toggleMenu()">☰ {{ __('Settings') }}</h2>
            <div class="settings-menu">
                <button onclick="showSection('PercentageTarget')"
                    style="background: var(--primary-color); color: var(--text-secondary-color);">{{ __('Percentage target') }}</button>
                <button onclick="showSection('user_days')">{{ __('user days') }}</button>

                <button
                    onclick="showSection('targets_table')"
                    class="{{ $tab == 'targets_table' ? 'active' : '' }}">
                    {{ __('Targets') }}
                </button>
                @if ($remaining_diamonds_action)
                    <button onclick="showSection('remaining_diamonds')">{{ __('remaining diamonds') }}</button>
                @endif

            </div>
        </div>

        <div class="settings-content">
            <div id="PercentageTarget" class="settings-section active">

                <h3> {{ __('Percentage target') }}</h3>

                <form id="target-percentage-form" action="{{ route('admin.target-percentage') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @php
                        $errorMessage = $errors ? $errors->first('msg') : null;
                    @endphp
                    @if ($errorMessage)
                        <div class="alert alert-danger text-center" style="margin-bottom: 20px;"> {{ $errorMessage }}
                        </div>;
                    @endif
                    <div class="form">
                        <label>{{ __('Hours:') }} </label>
                        <input type="text" name="hours" value="{{ $hours }}" class="form-control">
                        <label>{{ __('Days:') }} </label>
                        <input type="text" name="days" value="{{ $days }}" class="form-control">
                        <label>{{ __('Moments:') }} </label>
                        <input type="text" name="moments" value="{{ $moments }}" class="form-control">
                        <label>{{ __('Reels:') }} </label>
                        <input type="text" name="reels" value="{{ $reels }}" class="form-control">
                        <label>{{ __('Diamonds') }} </label>
                        <input type="text" name="diamonds" value="{{ $diamonds }}" class="form-control">
                        <button type="submit">{{ __('Save') }}</button>
                    </div>

                </form>
            </div>

            <div id="user_days" class="settings-section ">

            <!-- <h3> {{ __('Percentage target') }}</h3> -->

            <form id="target-percentage-form" action="{{ route('admin.app.settings.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @php
                    $errorMessage = $errors ? $errors->first('msg') : null;
                @endphp
                @if ($errorMessage)
                     <div class="alert alert-danger text-center" style="margin-bottom: 20px;"> {{ $errorMessage }}
                    </div>;
                @endif
                <div class="form">
                    <label>{{ __('Hours:') }} </label>
                    <input type="number" name="hours_days" value="{{ $hoursDays }}" class="form-control">
                    <button type="submit">{{ __('Save') }}</button>
                </div>
            </form>
            </div>
                @if ($remaining_diamonds_action)
                    <div id="remaining_diamonds" class="settings-section p-4 shadow-sm rounded bg-white">

                        <!-- Header Row -->
                        <div class="d-flex justify-content-between align-items-center mb-4">

                            <a href="{{ admin_url('remaining-diamonds') }}" class="btn btn-outline-primary fw-bold px-4 py-2">
                                <i class="fa fa-history me-1"></i> {{ __('History') }}
                            </a>
                        </div>

                        <form method="POST" action="{{ admin_url('remaining-diamond-settings/save') }}">
                            @csrf

                            <!-- Exchange Type -->
                            <div class="mb-3">
                                <label for="remaining_diamonds" class="form-label fw-semibold">{{ __('exchange to') }}</label>

                                <select name="remaining_diamonds" id="remaining_diamonds" class="form-select form-select-lg">
                                    <option value="nothing" {{ $settings['remaining_diamonds']=='nothing' ? 'selected' : '' }}>
                                        {{ __('Do not make any thing') }}
                                    </option>
                                    <option value="coins" {{ $settings['remaining_diamonds']=='coins' ? 'selected' : '' }}>
                                        {{ __('Coins') }}
                                    </option>
                                    <option value="diamonds" {{ $settings['remaining_diamonds']=='diamonds' ? 'selected' : '' }}>
                                        {{ __('Diamonds') }}
                                    </option>
                                </select>
                            </div>

                            <!-- Context Text -->
                            <div class="alert alert-info mt-3">
                                <i class="fa fa-info-circle me-1"></i>
                                {{ __('Remaining diamonds from last month that the host user can convert to coins, keep as diamonds, or leave unchanged.') }}
                            </div>

                            <!-- Save Button -->
                            <div class="d-flex justify-content-end mt-4">
                                <button type="submit" class="btn btn-success btn-lg px-4 fw-bold shadow">
                                    <i class="fa fa-check-circle me-1"></i> {{ __('Save') }}
                                </button>
                            </div>

                        </form>
                    </div>

                @endif



            <div id="targets_table" class="settings-section">
                <h3>{{ __('Targets table') }}</h3>

                {!! $targetGrid !!}
            </div>

        <div id="imageModal" class="modal" onclick="closeFullScreen()">
            <span class="close">&times;</span>
            <img class="modal-content" id="fullImage">
        </div>




        <script>


            function previewImage(input, previewId) {
                const preview = document.getElementById(previewId);
                const file = input.files[0];

                if (file) {
                    const reader = new FileReader();

                    reader.onload = function(e) {
                        preview.src = e.target.result;
                        preview.style.display = 'block';
                    }

                    reader.readAsDataURL(file);
                }
            }

            function openFullScreen(imgElement) {
                var modal = document.getElementById("imageModal");
                var modalImg = document.getElementById("fullImage");

                modal.style.display = "block";
                modalImg.src = imgElement.src;
            }

            function openLanguageTab(evt, languageCode) {
                // Hide all tab contents
                document.querySelectorAll('.tab-content').forEach(content => {
                    content.classList.remove('active');
                });

                // Remove active class from all buttons
                document.querySelectorAll('.tab-button').forEach(button => {
                    button.classList.remove('active');
                });

                // Show the current tab and mark button as active
                document.getElementById(languageCode).classList.add('active');
                evt.currentTarget.classList.add('active');
            }

        </script>

        <!-- كود JavaScript -->
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                // Function to get query parameter by name
                function getQueryParam(name) {
                    const urlParams = new URLSearchParams(window.location.search);
                    return urlParams.get(name);
                }

                // Get the 'firsttab' parameter from URL or default to 'brandSettings'
                const activeTab = getQueryParam("firsttab") || "PercentageTarget";

                // Show the selected tab
                showSection(activeTab);
            });

            function showSection(sectionId) {
                // Remove active class from all sections
                document.querySelectorAll('.settings-section').forEach(section => {
                    section.classList.remove('active');
                });

                // Add active class to the selected section
                document.getElementById(sectionId).classList.add('active');

                // ✅ Reset ALL buttons
                document.querySelectorAll('.settings-menu button').forEach(button => {
                    button.classList.remove('active');
                    button.style.backgroundColor = '';
                    button.style.color = '';
                });

                // ✅ Find button by checking onclick content
                document.querySelectorAll('.settings-menu button').forEach(button => {
                    const onclick = button.getAttribute('onclick');
                    if (onclick && onclick.includes(sectionId)) {
                        button.classList.add('active');
                        button.style.backgroundColor = 'var(--primary-color)';
                        button.style.color = 'var(--text-secondary-color)';
                    }
                });

                if (sectionId === 'Badges') {
                    const EnglishTabBtn = document.querySelector('.tab-button[onclick*="en"]');
                    if (EnglishTabBtn) {
                        EnglishTabBtn.click();
                    }
                }

                const url = new URL(window.location);
                url.searchParams.set("firsttab", sectionId);
                window.history.pushState({}, "", url);
            }

            function openFullScreen(imgElement) {
                var modal = document.getElementById("imageModal");
                var modalImg = document.getElementById("fullImage");

                modal.style.display = "block";
                modalImg.src = imgElement.src;
            }

            function closeFullScreen() {
                document.getElementById("imageModal").style.display = "none";
            }

            document.getElementById('target-percentage-form').addEventListener('submit', function(e) {
                    e.preventDefault(); // إيقاف الإرسال مؤقتًا

                    const hours = parseFloat(document.querySelector('input[name="hours"]').value) || 0;
                    const days = parseFloat(document.querySelector('input[name="days"]').value) || 0;
                    const moments = parseFloat(document.querySelector('input[name="moments"]').value) || 0;
                    const reels = parseFloat(document.querySelector('input[name="reels"]').value) || 0;
                    const diamonds = parseFloat(document.querySelector('input[name="diamonds"]').value) || 0;

                    const total = hours + days + moments + reels + diamonds;

                    if (total !== 100) {
                        Swal.fire({
                            icon: 'error',
                            title: 'تحذير',
                            text: "{{ __('total_percentage_must_be_100') }}",
                            confirmButtonText: 'حسنًا'
                        });
                        return;
                    }

                    e.target.submit();
                });

            $(document).on('change', '#stopCharge,#stopInviteCode,#stopTransferSalary,#stopGiftCheckbox', function () {

                const id        = this.id;
                const isChecked = $(this).is(':checked');

                const map = {
                    stopCharge:         ['/admin/send-request-stop-charge',  'stop_charge'],
                    stopInviteCode:     ['/admin/send-request-invite-code',        'stop_invite_code'],
                    stopTransferSalary: ['/admin/send-request-transfer-salary','transfer_salary'],
                    stopGiftCheckbox:   ['/admin/close-open-gift',           'close_open_gifts'],
                };

                const [url, key] = map[id];

                $.ajax({
                    url,
                    type: 'POST',
                    data: { [key]: isChecked },
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
                })
                    .done(()   => toastr.success('Saved'))
                    .fail(err => toastr.error('Error'));
            });

        </script>
    </div>
</body>
