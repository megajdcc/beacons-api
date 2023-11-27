<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\{Auth, DB};
use App\Models\{Rol, Permiso};
use SebastianBergmann\Type\NullType;

class RolController extends Controller
{


    public function getRol(Rol $role)
    {
        $role->permisos->groupBy('panel.panel');
        $role->usuarios;

        return response()->json($role);
    }

    public function fetchData(Request $request)
    {

        $datos = $request->all();

        $paginator = Rol::where([
            ['nombre', 'LIKE', '%' . $datos['q'] . '%', 'OR'],
        ])
            ->with(['permisos', 'usuarios'])
            ->orderBy($datos['sortBy'], $datos['isSortDirDesc'] ? 'desc' : 'asc')
            ->paginate($datos['perPage']  == 0 ? 10000 : $datos['perPage']);

        $roles = $paginator->items();

        return response()->json([
            'roles' => $roles,
            'total' => $paginator->total()
        ]);
    }

    public function getRoles()
    {

        $roles = Rol::get();
        $rols = collect([]);
        if (Auth::user()->rol->nombre == 'Desarrollador') {
            $rols = $roles;
        } else {
            foreach ($roles as $rol) {

                if ($rol->nombre != 'Desarrollador') {
                    $rols->push($rol);
                }
            }
        }



        return response()->json($rols);
    }

    public function roles()
    {
        $roles = Rol::get();

        $rols = collect([]);
        if (Auth::user()->rol->nombre == 'Desarrollador') {
            $rols = $roles;
            foreach ($rols as $key => $rol) {
                $rol->permisos;
            }
        } else {

            foreach ($roles as $rol) {
                $rol->permisos;

                if ($rol->nombre != 'Desarrollador') {

                    $rols->push($rol);
                }
            }
        }
        return response()->json($rols);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $datos = $this->validar($request);

        try {
            DB::beginTransaction();

            $role  = Rol::create([
                'nombre' => $datos['nombre'],
            ]);

            if (isset($datos['permisos'])) {

                foreach ($datos['permisos'] as $key => $permiso) {

                    $permision = Permiso::where('nombre', $permiso['module'])->first();

                    $actions = [];

                    // dd($permiso);
                    if ($permiso['read']) {
                        array_push($actions, 'read');
                    }

                    if ($permiso['write']) {
                        array_push($actions, 'write');
                    }

                    if ($permiso['update']) {
                        array_push($actions, 'update');
                    }

                    if ($permiso['delete']) {
                        array_push($actions, 'delete');
                    }

                    $role->permisos()->attach($permision->id, ['actions' => json_encode($actions)]);
                }
            }


            $role->permisos;



            DB::commit();
            $result = true;

            $message = "El rol se ha creado con éxito";
        } catch (\Exception $e) {
            DB::rollBack();
            $result = false;
            $message = 'EL rol no se pudo crear';
        }

        return response()->json(['result' => $result, 'rol' => ($result) ? $role : null, 'message' => $message]);
    }

    private function validar(Request $request, Rol $role = null): array
    {
        return $request->validate([
            'nombre' => ['required', $role ?  Rule::unique('rols', 'nombre')->ignore($role) : 'unique:rols,nombre'],
            'permisos.*' => 'required',
        ], [
            'nombre.required' => 'Este campo es necesario',
            'nombre.unique' => 'Este rol ya está creado, no puede crear uno igual',
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Rol $role
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Rol $role)
    {
        $datos = $this->validar($request, $role);

        try {

            DB::beginTransaction();

            $role->nombre = $datos['nombre'];

            $role->save();

            if (isset($datos['permisos'])) {

                $role->permisos()->detach();


                foreach ($datos['permisos'] as $key => $permiso) {

                    $permision = Permiso::where('nombre', $permiso['module'])->first();

                    $actions = [];

                    // dd($permiso);
                    if ($permiso['read']) {
                        array_push($actions, 'read');
                    }

                    if ($permiso['write']) {
                        array_push($actions, 'write');
                    }

                    if ($permiso['update']) {
                        array_push($actions, 'update');
                    }

                    if ($permiso['delete']) {
                        array_push($actions, 'delete');
                    }

                    $role->permisos()->attach($permision->id, ['actions' => json_encode($actions)]);
                }
            }

            foreach (User::where('rol_id', $role->id)->get() as $usuario) {
                $usuario->asignarPermisosPorRol();
            }

            $role->permisos;



            DB::commit();
            $result = true;

            $message = "El rol se ha actualizado con exito";
        } catch (\Exception $e) {
            DB::rollBack();
            $result = false;
            $message = 'EL rol no se pudo actualizar';

            dd($e->getMessage());
        }

        return response()->json(['result' => $result, 'rol' => ($result) ? $role : null, 'message' => $message]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Rol  $rol
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, Rol $role)
    {


        try {
            DB::beginTransaction();
            $role->delete();

            DB::commit();
            $result = true;
        } catch (\Exception $e) {
            DB::rollBack();
            $result = true;
        }

        return response()->json(['result' => $result]);
    }

    public function revocarPermiso(Request $request, Permiso $permiso, Rol $role)
    {

        if ($request->ajax()) {

            // $role->revokePermissionTo($permiso);

            return json_encode(['result' => true]);
        }
    }
}
