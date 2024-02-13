<?php
namespace App\Filters;

use Illuminate\Http\Request;


class ApiFilter {
   
    protected $allowedparams =[];

    protected $column_Map = [];

    protected $operator_Map =[
        'eq' => '=',
        'lt'=>'<',
        'lte' => '<=',
        'gt' => '>',
        'gte'=> '>',
        'ne'=> '!='
    ];
   
    public function transform(Request $request){
        $eloQuery = [];

        foreach($this->allowedparams as $param => $operators){
            $query = $request->query($param);
            
            if(!isset($query)){
                continue;
            }

            $column = $this->column_Map [$param] ?? $param;

            foreach($operators as $operator){
                if(isset($query[$operator])){
                    $eloQuery[] = [$column, $this->operator_Map[$operator], $query[$operator]]; 
                }
            }
        }

        return $eloQuery;
    }
}