<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;


use App\Models\Permiso;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;

use Illuminate\Support\Facades\DB;

class PermisoController extends Controller
{



    public function getPermiso(Permiso $permiso)
    {
        $permiso->usuarios;
        $permiso->roles;

        return response()->json($permiso);
    }

    public function fetchData(Request $request)
    {

        $datos = $request->all();

        $paginator = Permiso::whereHas('panel', function (Builder $query) use ($datos) {
            $query->where([
                ['panel', 'LIKE', "%{$datos['q']}%", "OR"]
            ]);
        })
            ->orWhere('nombre', "LIKE", "%{$datos['q']}%")
            ->with(['roles', 'usuarios', 'panel'])
            ->orderBy($datos['sortBy'], $datos['isSortDirDesc'] ? 'desc' : 'asc')
            ->paginate($datos['perPage'] ?: 1000);


        return response()->json([
            'permisos' => $paginator->items(),
            'total' => $paginator->total()
        ]);
    }


    private function  validar(Request $request, Permiso $permiso = null): array
    {

        return $request->validate([
            'nombre' => ['required', !is_null($permiso) ? Rule::unique('permisos', 'nombre')->ignore($permiso->id) : 'unique:permisos,nombre'],
            'panel_id' => 'required'
        ], [
            'nombre.unique' => 'El nombre del permiso ya está registrado, inténte con otro'
        ]);
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $datos  = $this->validar($request);
        try {
            DB::beginTransaction();
            $permiso  = Permiso::create($datos);

            DB::commit();
            $result = true;
            $message = 'Se ha creado con éxito el permiso';
        } catch (\Exception $e) {
            DB::rollBack();
            $message = 'No se ha podido registrar el permiso';
            $result = true;
        }

        return response()->json(['result' => $result, 'permiso' => ($result) ? $permiso : null, 'message' => $message]);
    }



    public function getPermisos()
    {
        $data = [
            'permisos' => Permiso::get(),
        ];


        return response()->json($data);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Permiso $permiso)
    {

        try {
            DB::beginTransaction();
            $permiso->update($this->validar($request, $permiso));

            DB::commit();
            $message = 'Se ha actualizado con éxito el permiso';
            $result = true;
        } catch (\Exception $e) {
            DB::rollBack();
            $message = 'No se pudo actualizar el permiso';

            $result = false;

            dd($e->getMessage());
        }

        return response()->json(['result' => $result, 'permiso' => ($result) ? $permiso : null, 'message' => $message]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, Permiso $permiso)
    {

        try {
            DB::beginTransaction();
            $permiso->delete();
            DB::commit();
            $result = true;
        } catch (\Exception $e) {
            DB::rollBack();
            $result = false;
        }

        return response()->json(['result' => $result]);
    }





    public function getPermissions()
    {

        return response()->json(Permiso::get());
    }
}
