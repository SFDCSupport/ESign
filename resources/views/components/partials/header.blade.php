@props([
    'isSigningRoute' => false,
])

<header
    class="navbar sticky-top bg-white flex-md-nowrap p-0 shadow navbar-header"
    data-bs-theme="dark"
>
    @if (! $isSigningRoute)
        <div class="container-fluid justify-content-center">
            <a href="/" class="navbar-brand">
                <img
                    src="{{ url(rsc('/img/shell-logo.png')) }}"
                    class="header-logo"
                />
            </a>

            <ul class="navbar-nav flex-row d-md-none">
                <li class="nav-item text-nowrap">
                    <button
                        class="nav-link px-3 text-white"
                        type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#navbarSearch"
                        aria-controls="navbarSearch"
                        aria-expanded="false"
                        aria-label="Toggle search"
                    >
                        <svg class="bi"><use xlink:href="#search" /></svg>
                    </button>
                </li>
                <li class="nav-item text-nowrap">
                    <button
                        class="nav-link px-3 text-white"
                        type="button"
                        data-bs-toggle="offcanvas"
                        data-bs-target="#sidebarMenu"
                        aria-controls="sidebarMenu"
                        aria-expanded="false"
                        aria-label="Toggle navigation"
                    >
                        <svg class="bi"><use xlink:href="#list" /></svg>
                    </button>
                </li>
            </ul>

            <div id="navbarSearch" class="navbar-search w-100 collapse">
                <input
                    class="form-control w-100 rounded-0 border-0"
                    type="text"
                    placeholder="Search"
                    aria-label="Search"
                />
            </div>
        </div>
    @else
        <div
            class="container-fluid pt-2 mb-2 text-center"
            style="display: block"
        >
            <a href="/">
                <img
                    src="{{ url(rsc('/img/shell-logo.png')) }}"
                    class="header-logo"
                />
            </a>
        </div>
    @endif
</header>
