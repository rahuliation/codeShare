<?php
namespace App\CustomTrait;
use Illuminate\Support\Facades\DB;
use App\Scopes\LangScope;
use App\Http\Helpers\Helper;

trait MultiLanguage {

    private static $lang_id;

    public function storeLang($data)
    {
        $table=$this->getTable();
        $data[str_singular($table).'_id']=$this->id;
        $multi_lang_data=[];
        $index=0;
        foreach (Helper::get_languages() as $key => $lang)
        {
            $multi_lang_data[$index]=$data;
            $multi_lang_data[$index]['lang_id']=$key;
            $index++;
        }
       return DB::table($table.'_lang')->insert($multi_lang_data);
    }

    public function storeLangCurrent($data)
    {
        $table=$this->getTable();
        $data[str_singular($table).'_id']=$this->id;
        $lang_id= static::$lang_id;
        $data['lang_id']=$lang_id;
        return DB::table($table.'_lang')->insert($data);
    }

    public function updateLang($data)
    {
        $table=$this->getTable();
        $lang_id= static::$lang_id;
        return DB::table($table.'_lang')
            ->where(str_singular($table).'_id', $this->id)
            ->where('lang_id', $lang_id)
            ->update($data);
    }

    public function deleteLang()
    {
        $table=$this->getTable();
        return DB::table($table.'_lang')
            ->where(str_singular($table).'_id', $this->id)
            ->delete();
    }

    public function deleteLangCurrent()
    {
        $lang_id= static::$lang_id;;
        $table=$this->getTable();
        return DB::table($table.'_lang')
            ->where(str_singular($table).'_id', $this->id)
            ->where('lang_id', $lang_id)
            ->delete();
    }

    public function scopeLang($builder){
        $lang_id= Helper::active_lang();
        $table=$this->getTable();
        $lang_table=$table.'_lang';
        $builder->join($lang_table, $table.'.id', '=', $lang_table.'.'.str_singular($table).'_id')->where($lang_table.'.lang_id','=',$lang_id);
    }





    public static function MultiLanguage_boot()
    {
        static::$lang_id=Helper::active_lang();
        parent::boot();
        static::addGlobalScope(new LangScope);
    }

}
