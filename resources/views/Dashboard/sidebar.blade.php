<aside class="sidebar">
    <button type="button" class="sidebar-close-btn">
        <iconify-icon icon="radix-icons:cross-2"></iconify-icon>
    </button>
    <div class="sidebar-menu-area" id='sidebar'>
        <ul class="sidebar-menu" id="sidebar-menu">
            <li>
                <a href="{{ Route('dashboard') }}"  @if(in_array(Route::current()->getName(), ['dashboard'])) class="active-page" @endif>
                    <iconify-icon icon="solar:home-smile-angle-outline" class="menu-icon"></iconify-icon>
                    <span>Dashboard</span>
                </a>
            </li>
            <li>
                <a href="{{ Route('admins') }}" @if(in_array(Route::current()->getName(), ['admins','add-admin','edit-admin'])) class="active-page" @endif>
                    <iconify-icon icon="solar:user-check-bold-duotone" class="menu-icon"></iconify-icon>
                    <span>Admins</span>
                </a>
            </li>
            <li>
                <a href="{{ Route('employees') }}" @if(in_array(Route::current()->getName(), ['employees','add-employee','edit-employee'])) class="active-page" @endif>
                    <iconify-icon icon="solar:user-bold" class="menu-icon"></iconify-icon>
                    <span>Employees</span>
                </a>
            </li>
            <li>
                <a href="{{ Route('users') }}" @if(in_array(Route::current()->getName(), ['users','add-user','edit-user'])) class="active-page" @endif>
                    <iconify-icon icon="solar:user-circle-outline" class="menu-icon"></iconify-icon>
                    <span>Users</span>
                </a>
            </li>


            <li class="sidebar-menu-group-title">Products</li>
            <li>
                <a href="{{ Route('be-categories') }}" @if(in_array(Route::current()->getName(), ['categories','add-category','edit-category'])) class="active-page" @endif>
                    <iconify-icon icon="solar:checklist-minimalistic-linear" class="menu-icon"></iconify-icon>
                    <span>Categories</span>
                </a>
            </li>
            <li>
                <a href="{{ Route('products') }}" @if(in_array(Route::current()->getName(), ['products','add-product','edit-product'])) class="active-page" @endif>
                    <iconify-icon icon="solar:bag-check-outline" class="menu-icon"></iconify-icon>
                    <span>Products</span>
                </a>
            </li>

            <li class="sidebar-menu-group-title">Orders</li>
            <li>
                <a href="{{ Route('orders') }}" @if(in_array(Route::current()->getName(), ['orders','add-order','edit-order'])) class="active-page" @endif>
                    <iconify-icon icon="hugeicons:invoice-03" class="menu-icon"></iconify-icon>
                    <span>Orders</span>
                </a>
            </li>

        </ul>
    </div>
</aside>
