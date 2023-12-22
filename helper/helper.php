<?php
if( ! function_exists('dataType') ){
    function dataType(){
        return [
            'integer' => 'unsignedBigInteger',
            'string' => 'string',
            'dateTime' => 'dateTime',
            'date' => 'date',
            'float' => 'float',
            'double' => 'double'
        ];
    }
}
if( ! function_exists('validationRule') ){
    function validationRule(){
        return [
            'required',
            'nullable',
            'file',
            'string',
            'integer',
            'date'
        ];
    }
}
if ( ! function_exists('insert_row') ){
    /**
     * @param $model,$data
     * @return \Illuminate\Http\Response|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
     */
    function insert_row($model,$data, $message = null)
    {
        $inserted_data = $model::create($data);
        return $inserted_data;
    }
}

if ( ! function_exists('update_row') ){
    /**
     * @param $model,$request_data,$id
     * @return \Illuminate\Http\Response|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
     */
    function update_row($model,$request_data,$id,$message = null)
    {
        $data = $model::find($id);
        $data->update($request_data);
        return $data;
    }
}

if ( ! function_exists('get_single_row') ){
    /**
     * @param $model,$id
     * @return \Illuminate\Http\Response|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
     */
    function get_single_row($model,$id)
    {
        $data = $model::find($id);
        return $data;
    }
}

if ( ! function_exists('delete_row') ){
    /**
     * @param $model,$id
     * @return \Illuminate\Http\Response|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
     */
    function delete_row($model,$id,$message = null)
    {
        $data = $model::find($id);
        $data->delete();
    }
}
if ( ! function_exists('limit') ){
    /**
     * @param $query
     * @return int|mixed $paginate,
     */
    function limit($query){
        $paginate = 10;

        if (array_key_exists('limit',$query)){
            if ($query['limit']){
                $paginate = $query['limit'];
            }
        }

        return $paginate;
    }
}
