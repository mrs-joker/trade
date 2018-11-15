# Trade
rbac

        Trade::make('rbac')->executeCommand('permission', [])->update(['id' => 1, 'display_name' => 'display_name','description'=>'goiyugoiuyg']);
        Trade::make('rbac')->executeCommand('role', [])->add(['name' => 'test', 'display_name' => 'display_name','description'=>'goiyugoiuyg']);
        Trade::make('rbac')->executeCommand('role', [])->update(['id' => 1, 'display_name' => 'display_name', 'description' => 'qqqq']);
        Trade::make('rbac')->executeCommand('role', [])->attachPermission(1, 2);
        Trade::make('rbac')->executeCommand('role', [])->syncPermission(1, [1]);
        array_pluck(Trade::make('rbac')->executeCommand('role')->cachedPermissions(1)->toArray(),'id');
        Trade::make('rbac')->executeCommand('role')->destory(1, true);
        Trade::make('rbac')->executeCommand('role')->restore(1);
        Trade::make('rbac')->executeCommand('role')->hasPermission(1,['test1','testq']);
        Trade::make('rbac')->executeCommand('user')->cachedRoles(1);
        Trade::make('rbac')->executeCommand('user')->hasRole(1, ['test']);
        Trade::make('rbac')->executeCommand('user')->can(1, ['test', 'ff']);
        Trade::make('rbac')->executeCommand('user')->detachRole(1, 1);
        Trade::make('rbac')->executeCommand('user')->attachRole(1, 1);


blade


                        @role('test')
                        <p>This is visible to users with the admin role. Gets translated to</p>
                        @endrole

                        @permission('test111')
                        <p>permission</p>
                        @endpermission