# Trade
rbac
                      dd(Trade::make('rbac')->executeCommand('permission', [])->update(['id' => 1, 'display_name' => 'display_name','description'=>'goiyugoiuyg']));
                      dd(Trade::make('rbac')->executeCommand('role', [])->add(['name' => 'test', 'display_name' => 'display_name','description'=>'goiyugoiuyg']));
                      dd(Trade::make('rbac')->executeCommand('role', [])->update(['id' => 1, 'display_name' => 'display_name', 'description' => 'qqqq']));
                      dd(Trade::make('rbac')->executeCommand('role', [])->attachPermission(1, 2));
                      dd(Trade::make('rbac')->executeCommand('role', [])->syncPermission(1, [1]));
                      dd(array_pluck(Trade::make('rbac')->executeCommand('role')->cachedPermissions(1)->toArray(),'id'));
                      dd(Trade::make('rbac')->executeCommand('role')->destory(1, true));
                      dd(Trade::make('rbac')->executeCommand('role')->restore(1));
                      dd(Trade::make('rbac')->executeCommand('role')->hasPermission(1,['test1','testq']));
                      dd(Trade::make('rbac')->executeCommand('user')->cachedRoles(1));
                      dd(Trade::make('rbac')->executeCommand('user')->hasRole(1, ['test']));
                      dd(Trade::make('rbac')->executeCommand('user')->can(1, ['test', 'ff']));
                      dd(Trade::make('rbac')->executeCommand('user')->detachRole(1, 1));
                      dd(Trade::make('rbac')->executeCommand('user')->attachRole(1, 1));



                        @role('test')
                        <p>This is visible to users with the admin role. Gets translated to</p>
                        @endrole

                        @permission('test111')
                        <p>permission</p>
                        @endpermission
