@php
    use Illuminate\Support\Facades\Route;
@endphp

<style>
    /* =========================
   SIDEBAR BASE
========================= */
    .app-sidebar {
        background: linear-gradient(180deg, #0f172a, #020617);
        color: #000000;
        border-right: 1px solid rgba(255, 255, 255, .06);
        scrollbar-width: thin;
    }

    .app-sidebar::-webkit-scrollbar {
        width: 5px;
    }

    .app-sidebar::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, .15);
        border-radius: 10px;
    }

    /* =========================
   BRAND LOGO
========================= */
    .side-header {
        padding: 20px;
        text-align: center;
        border-bottom: 1px solid rgba(255, 255, 255, .08);
    }

    .side-header img {
        transition: transform .3s ease;
    }

    .side-header img:hover {
        transform: scale(1.05);
    }

    /* =========================
   SECTION TITLES
========================= */
    .side-menu h3 {
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 1.4px;
        color: #94a3b8;
        margin: 20px 15px 10px;
    }

    /* =========================
   MENU ITEMS
========================= */
    .side-menu__item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 18px;
        margin: 4px 10px;
        border-radius: 10px;
        font-weight: 500;
        transition: all .25s ease;
    }

    .side-menu__icon {
        font-size: 18px;
        min-width: 22px;
        opacity: .85;
    }

    /* Hover */
    .side-menu__item:hover {
        background: rgba(99, 102, 241, .12);
        color: #fff;
        transform: translateX(4px);
    }

    /* Active */
    .side-menu__item.active {
        background: linear-gradient(135deg, #6366f1, #4f46e5);
        color: #fff;
        box-shadow: 0 8px 20px rgba(79, 70, 229, .35);
    }

    .side-menu__item.active .side-menu__icon {
        opacity: 1;
    }

    /* =========================
   SUB MENUS
========================= */
    .slide-menu,
    .sub-slide-menu {
        padding-left: 12px;
    }

    .slide-item,
    .sub-slide-item {
        padding: 9px 16px;
        margin: 4px 10px;
        border-radius: 8px;
        font-size: 14px;
        color: #082081;
        transition: all .2s ease;
    }

    .slide-item:hover,
    .sub-slide-item:hover {
        background: rgba(255, 255, 255, .08);
        color: #fff;
    }

    /* =========================
   ANGLE ICON
========================= */
    .angle,
    .sub-angle {
        margin-left: auto;
        transition: transform .3s ease;
    }

    .slide.open>a .angle {
        transform: rotate(90deg);
    }

    /* =========================
   SEARCH BAR
========================= */
    #menuSearching {
        margin: 12px;
        padding: 10px 14px;
        border-radius: 10px;
        background: rgba(255, 255, 255, .08);
        color: #fff;
    }

    #menuSearching::placeholder {
        color: #000000;
    }
</style>

<!--APP-SIDEBAR-->
<div class="sticky">
    <div class="app-sidebar__overlay" data-bs-toggle="sidebar"></div>
    <div class="app-sidebar" style="overflow: scroll">
        <div class="side-header">
            <a class="header-brand1" href="{{ route('admin.dashboard') }}">
                <img src="{{ asset(settings('logo') ?? 'default/logo.svg') }}" id="header-brand-logo" alt="logo"
                    width="{{ settings('logo_width') ?? 67 }}" height="{{ settings('logo_height') ?? 67 }}">
            </a>
        </div>
        <div class="main-sidemenu">
            <ul id="customMenulist" class="side-menu"></ul>
        </div>
        <div class="main-sidemenu">
            <ul class="side-menu mt-2">

                <li>
                    <h3>Menu</h3>
                </li>
                <li class="slide">
                    <a class="side-menu__item {{ request()->routeIs('dashboard') ? 'has-link active' : '' }}"
                        href="{{ route('admin.dashboard') }}">
                        <i class="fa-solid fa-house side-menu__icon"></i>
                        <span class=" side-menu__label">Dashboard</span>
                    </a>
                </li>
                <li class="slide">
                    <a class="side-menu__item {{ request()->routeIs('admin.news.*') ? 'has-link active' : '' }}"
                        href="{{ route('admin.news.index') }}">
                        <i class="fa-solid fa-newspaper side-menu__icon"></i>
                        <span class="side-menu__label">News</span>
                    </a>
                </li>

                <li class="slide">
                    <a class="side-menu__item {{ request()->routeIs('admin.chat.*') ? 'has-link active' : '' }}"
                        href="{{ route('admin.chat.index') }}">
                        <i class="fa-brands fa-rocketchat side-menu__icon"></i>
                        <span class="side-menu__label">Chat</span>
                    </a>
                </li>

                @role('admin')
                    <li class="sliden {{ env('ACCESS') === false ? 'd-none' : '' }}">
                        <a class="side-menu__item {{ request()->routeIs('admin.users.*') ? 'has-link active' : '' }}"
                            data-bs-toggle="slide" href="#">
                            <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 512 512"
                                fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                stroke-width="32">
                                <rect width="416" height="416" rx="48" ry="48" />
                                <path d="m192 256 128 0" />
                            </svg>
                            <span class="side-menu__label">User Access</span><i class="angle fa fa-angle-right"></i>
                        </a>

                        <ul class="slide-menu">
                            <li><a href="{{ route('admin.users.index') }}" class="slide-item">User</a></li>
                            <li><a href="{{ route('admin.roles.index') }}" class="slide-item">Roll</a></li>
                            <li><a href="{{ route('admin.permissions.index') }}" class="slide-item">Permission</a></li>
                        </ul>
                    </li>
                @endrole
                @role('admin')
                    <li class="slide">
                        <a class="side-menu__item {{ request()->routeIs('admin.setting.*') ? 'has-link active' : '' }}"
                            data-bs-toggle="slide" href="#">
                            <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 512 512">
                                <path
                                    d="M495.9 166.6c3.2 8.7 .5 18.4-6.4 24.6l-43.3 39.4c1.1 8.3 1.7 16.8 1.7 25.4s-.6 17.1-1.7 25.4l43.3 39.4c6.9 6.2 9.6 15.9 6.4 24.6c-4.4 11.9-9.7 23.3-15.8 34.3l-4.7 8.1c-6.6 11-14 21.4-22.1 31.2c-5.9 7.2-15.7 9.6-24.5 6.8l-55.7-17.7c-13.4 10.3-28.2 18.9-44 25.4l-12.5 57.1c-2 9.1-9 16.3-18.2 17.8c-13.8 2.3-28 3.5-42.5 3.5s-28.7-1.2-42.5-3.5c-9.2-1.5-16.2-8.7-18.2-17.8l-12.5-57.1c-15.8-6.5-30.6-15.1-44-25.4L83.1 425.9c-8.8 2.8-18.6 .3-24.5-6.8c-8.1-9.8-15.5-20.2-22.1-31.2l-4.7-8.1c-6.1-11-11.4-22.4-15.8-34.3c-3.2-8.7-.5-18.4 6.4-24.6l43.3-39.4C64.6 273.1 64 264.6 64 256s.6-17.1 1.7-25.4L22.4 191.2c-6.9-6.2-9.6-15.9-6.4-24.6c4.4-11.9 9.7-23.3 15.8-34.3l4.7-8.1c6.6-11 14-21.4 22.1-31.2c5.9-7.2 15.7-9.6 24.5-6.8l55.7 17.7c13.4-10.3 28.2-18.9 44-25.4l12.5-57.1c2-9.1 9-16.3 18.2-17.8C227.3 1.2 241.5 0 256 0s28.7 1.2 42.5 3.5c9.2 1.5 16.2 8.7 18.2 17.8l12.5 57.1c15.8 6.5 30.6 15.1 44 25.4l55.7-17.7c8.8-2.8 18.6-.3 24.5 6.8c8.1 9.8 15.5 20.2 22.1 31.2l4.7 8.1c6.1 11 11.4 22.4 15.8 34.3zM256 336a80 80 0 1 0 0-160 80 80 0 1 0 0 160z" />
                            </svg>
                            <span class="side-menu__label">Settings</span><i class="angle fa fa-angle-right"></i>
                        </a>
                        <ul class="slide-menu">
                            <li><a href="{{ route('admin.setting.general.index') }}" class="slide-item">General
                                    Settings</a></li>
                            <li><a href="{{ route('admin.setting.env.index') }}" class="slide-item">Environment
                                    Settings</a></li>
                            <li><a href="{{ route('admin.setting.logo.index') }}" class="slide-item">Logo Settings</a></li>
                            <li><a href="{{ route('admin.setting.profile.index') }}" class="slide-item">Profile
                                    Settings</a></li>
                            <li><a href="{{ route('admin.setting.mail.index') }}" class="slide-item">Mail Settings</a></li>
                            <li><a href="{{ route('admin.setting.stripe.index') }}" class="slide-item">Stripe Settings</a>
                            </li>
                            <li><a href="{{ route('admin.setting.firebase.index') }}" class="slide-item">Firebase
                                    Settings</a></li>
                            <li><a href="{{ route('admin.setting.social.index') }}" class="slide-item">Social Settings</a>
                            </li>
                            <li><a href="{{ route('admin.setting.google.map.index') }}" class="slide-item">Google Map
                                    Settings</a></li>
                            <li><a href="{{ route('admin.setting.captcha.index') }}" class="slide-item">Captcha
                                    Settings</a></li>
                            <li><a href="{{ route('admin.setting.signature.index') }}" class="slide-item">Signature
                                    Settings</a></li>
                            <li><a href="{{ route('admin.setting.other.index') }}" class="slide-item">Other Settings</a>
                            </li>
                            <li><a href="{{ route('plugins.index') }}" class='slide-item'>Manage Plugins</a></li>
                        </ul>
                    </li>
                @endrole

                @role('admin')
                    <li>
                        <h3>CMS</h3>
                    </li>
                    <li class="slide">
                        <a class="side-menu__item {{ request()->routeIs('admin.page.*') ? 'has-link active' : '' }}"
                            href="{{ route('admin.page.index') }}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor"
                                class="side-menu__icon" viewBox="0 0 16 16">
                                <path d="M15 14l-5-5-5 5v-3l10 -10z" />
                            </svg>
                            <span class="side-menu__label">Dynamic Page</span>
                        </a>
                    </li>
                    <li class="slide">
                        <a class="side-menu__item {{ request()->routeIs('admin.social.*') ? 'has-link active' : '' }}"
                            href="{{ route('admin.social.index') }}">
                            <i class="fa-solid fa-link side-menu__icon"></i>
                            <span class="side-menu__label">Social Link</span>
                        </a>
                    </li>
                    <li class="slide">
                        <a class="side-menu__item {{ request()->routeIs('admin.faq.*') ? 'has-link active' : '' }}"
                            href="{{ route('admin.faq.index') }}">
                            <i class="fa-solid fa-clipboard-question side-menu__icon"></i>
                            <span class="side-menu__label">FAQ</span>
                        </a>
                    </li>
                    <li class="slide">
                        <a class="side-menu__item" data-bs-toggle="slide" href="#">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor"
                                class="side-menu__icon" viewBox="0 0 16 16">
                                <path
                                    d="M7.5 5.5a.5.5 0 0 0-1 0v.634l-.549-.317a.5.5 0 1 0-.5.866L6 7l-.549.317a.5.5 0 1 0 .5.866l.549-.317V8.5a.5.5 0 1 0 1 0v-.634l.549.317a.5.5 0 1 0 .5-.866L8 7l.549-.317a.5.5 0 1 0-.5-.866l-.549.317zm-2 4.5a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1zm0 2a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1z" />
                                <path
                                    d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2M9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5z" />
                            </svg>
                            <span class="side-menu__label">CMS</span><i class="angle fa fa-angle-right"></i>
                        </a>
                        <ul class="slide-menu">
                            <li class="sub-slide">
                                <a class="sub-side-menu__item" data-bs-toggle="sub-slide" href="#"><span
                                        class="sub-side-menu__label">Home Page</span><i
                                        class="sub-angle fa fa-angle-right"></i></a>
                                <ul class="sub-slide-menu">
                                    <li><a href="{{ route('admin.cms.home.example.index') }}"
                                            class="sub-slide-item">Example Section</a></li>
                                    <li><a href="{{ route('admin.cms.home.intro.index') }}" class="sub-slide-item">Intro
                                            Section</a></li>
                                    <li><a href="{{ route('admin.cms.home.about.index') }}" class="sub-slide-item">About
                                            Section</a></li>
                                </ul>
                            </li>
                        </ul>
                    </li>
                @endrole


                @can('dev')
                    <li class="{{ env('APP_ENV') == 'production' ? 'd-none' : '' }}">
                        <h3>Dev Tools</h3>
                    </li>
                    <li class="slide">
                        <a class="side-menu__item {{ Request::routeIs('admin.curd.*') ? 'has-link active' : '' }}"
                            href="{{ route('admin.curd.index') }}">
                            <i class="fa-solid fa-database side-menu__icon"></i>
                            <span class="side-menu__label">CURD</span>
                        </a>
                    </li>
                    <li class="slide">
                        <a class="side-menu__item {{ Request::routeIs('ajax.gallery.*') ? 'has-link active' : '' }}"
                            href="{{ route('ajax.gallery.index') }}">
                            <i class="fa-solid fa-image side-menu__icon"></i>
                            <span class="side-menu__label">Image Gallery</span>
                        </a>
                    </li>
                    <li class="slide">
                        <a class="side-menu__item {{ Request::routeIs('admin.file-manager.*') ? 'has-link active' : '' }}"
                            href="{{ route('admin.file-manager') }}">
                            <i class="fa-solid fa-folder side-menu__icon"></i>
                            <span class="side-menu__label">File Manager</span>
                        </a>
                    </li>
                    <li class="slide {{ env('APP_ENV') == 'production' ? 'd-none' : '' }}">
                        <a class="side-menu__item {{ Request::routeIs('admin.livewire.crud.*') ? 'has-link active' : '' }}"
                            href="{{ route('admin.livewire.crud.index') }}">
                            <i class="fa-solid fa-image side-menu__icon"></i>
                            <span class="side-menu__label">Livewire</span>
                        </a>
                    </li>
                @endcan

                <li class="slide">
                    <hr />
                </li>
                <li class="slide">
                    <a class="side-menu__item" href="{{ route('logout') }}"
                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="fa-solid fa-arrow-right-from-bracket side-menu__icon"></i>
                        <span class="side-menu__label">Log out</span>
                    </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                </li>
                <li class="slide">
                    <hr />
                </li>
            </ul>
            <div class="slide-right" id="slide-right"><svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191"
                    width="24" height="24" viewBox="0 0 24 24">
                    <path d="M10.707 17.707 16.414 12l-5.707-5.707-1.414 1.414L13.586 12l-4.293 4.293z" />
                </svg>
            </div>
        </div>
    </div>
</div>
<!--/APP-SIDEBAR-->

<script>
    const menuSearchInput = document.getElementById('menuSearching');
    const customMenuList = document.getElementById('customMenulist');
    const menus = @json(App\Models\Menu::where('status', 1)->orderBy('id', 'DESC')->get());

    function sideMenu() {
        menus.forEach(menu => {
            if (menu.name.toLowerCase().includes(menuSearchInput.value.toLowerCase())) {
                customMenuList.innerHTML += `
                    <li class="slide">
                        <a class="side-menu__item" href="#">
                            <i class="fa-solid fa-bars side-menu__icon"></i>
                            <span class="side-menu__label">${menu.name}</span>
                        </a>
                    </li>
                `;
            }
        });
    }

    menuSearchInput.addEventListener('input', function() {
        customMenuList.innerHTML = '';
        if (menuSearchInput.value.trim() === '') {
            customMenuList.innerHTML = '';
        } else {
            sideMenu();
        }
    });
</script>
