<?php
namespace Api\Model;
use Think\Model;

class SchoolModel extends Model
{
    /**
     * 根据定位的经纬度计算店铺距离
     * @param $lon
     * @param $lat
     * @param $where
     * @return mixed
     *
     */
    public function getDistance($lon,$lat,$where,$limit){
        $model = D();
        $sql = "select *, ROUND(6378.138*2*ASIN(SQRT(POW(SIN(($lat*PI()/180-latitude*PI()/180)/2),2)+COS($lat*PI()/180)*COS(latitude*PI()/180)*POW(SIN(($lon*PI()/180-longitude*PI()/180)/2),2)))*1000) AS distance FROM ec_school".$where." order by distance asc ".$limit;

        $data = $model->query($sql);

        if(sizeof($data)>0){
            foreach ($data as $k=>$v){
                if ($v['distance'] <= 1000) {
                    $data[$k]['distance'] = $v['distance'].'m';    //米
                } else {
                    $data[$k]['distance'] = round($v['distance']/1000,1).'km';  //千米
                }
            }
        }else{
            $data=array();
        }

        return $data;
    }
}