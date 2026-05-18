<?php

namespace App\Transformer;

use App\Entity\Entity;

abstract class Transformer
{
    abstract protected function relations(): array;
    abstract protected function data(Entity $entity):array;
    public function transformOne(Entity $entity,array $with = []): array
    {
        $relations = $this->relations();
        $data = $this->data($entity);
       
        foreach($relations as $relation => $options){
            if ($this->inRelation($relation,$with)){
                $transformer = $options['transformer'];
                $method = $options['method'];
                $all = $options['all'] ?? false;
                $newWith = $with[$relation] ?? [];
                $transformer = new $transformer(); //ProductTransformer
                if($all){
                    $data[$relation] = $transformer->transformCollection($entity->$method()->toArray(),$newWith);
                }else{
                    $data[$relation] = $transformer->transformOne($entity->$method(),$newWith);
                }
            }
            
        }
        return $data;
    }

    private function inRelation(string $relation,array $with): bool
    {
        if (empty($with)) {
            return false;
        }
    
        foreach($with as $key => $item){
            if (is_string($key) && $key === $relation) {
                return true;
            }
            if(is_numeric($key) && $item === $relation) {
                return true;
            }
        }
        return false;
    }
    public function transformCollection(array $entities,array $with = []): array
    {
        $entitiesTransformed = [];
        foreach ($entities as $entity) {
            $entitiesTransformed[] = $this->transformOne($entity,$with);
        }
        return $entitiesTransformed;
    }
}