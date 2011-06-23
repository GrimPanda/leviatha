<?php
/**
    Author: Samuel Ferrell (huckfinnaafb@gmail.com)
    Purpose: Generate a JSON object of any item, including, but not limited to,
        item properties, related items, and statistical information.
**/
class LootModel extends RootModel {
    
    // Loot Options
    private $options = array(
        "verbose" => false,
        "spread" => 25,
        "count" => 6
    );
    
    // SQL Query Array
    private $query = array(
        "item"              => 
            "
                SELECT 
                    loot.id, 
                    loot.name, 
                    loot.urlname, 
                    loot.level, 
                    loot.levelreq, 
                    loot.rarity, 
                    loot.grade,
                    loot_types.type AS parent,
                    loot_types.class
                FROM loot 
                JOIN loot_types ON loot.type = loot_types.code 
                WHERE urlname = :item
            ",
            
        "properties"        => "SELECT * FROM loot_properties JOIN translate_loot_properties ON translate_loot_properties.property = loot_properties.property WHERE name = :item AND req_equip = 0 AND display = 1",
        "properties_set"    => "SELECT * FROM loot_properties JOIN translate_loot_properties ON translate_loot_properties.property = loot_properties.property WHERE name = :item AND req_equip > 0",
        "properties_family" => "SELECT * FROM loot_properties_family JOIN translate_loot_properties ON translate_loot_properties.property = loot_properties_family.property WHERE set_family = :family",
        "family"            => "SELECT * FROM relate_loot_set WHERE set_item = :item",
        "siblings"          => "SELECT loot.id, loot.urlname FROM relate_loot_set JOIN loot ON loot.name = relate_loot_set.set_item WHERE set_family = :family",
        "similar"           => "SELECT loot.id, loot.urlname FROM loot WHERE (level >= :level) AND (division = :division) AND (rarity != 'normal') NOT IN (name = :item) ORDER BY level ASC LIMIT :limit",
        "variants"          => "SELECT loot.id, loot.urlname FROM loot WHERE (class = :item) AND (rarity != 'normal') ORDER BY level DESC",
        
        "all"               => "
            SELECT 
                loot.id, 
                loot.name,
                loot.urlname, 
                loot.rarity, 
                loot.grade, 
                loot.level, 
                loot.levelreq, 
                loot.code,
                loot.type,
                loot.base,
                loot_types.type AS parent,
                loot_types.class
            FROM loot 
            JOIN loot_types ON loot.type = loot_types.code 
            ORDER BY rarity DESC, level DESC
        ",
            
        "types" => "
            SELECT DISTINCT 
                loot_types.type, 
                loot_types.code, 
                loot_types.kingdom 
            FROM loot_types 
            JOIN loot ON loot.type = loot_types.code 
            ORDER BY type
        "
    );
    
    /**
        Fetch All Relevant Item Data
            @return $this JSON Encoded Object
            @param $identifier string
            @param $options array
                Verbose (bool)
                    Default: True
                    Description: Toggle expensive property fetching
                Spread (integer)
                    Default: 25
                    Description: Level range when finding similar items
                Count (integer)
                    Default: 6
                    Description: Number of similar items to fetch
            @public
    **/
    public function item($identifier, $options = array()) {
        
        // Configurations
        $this->options = array_merge($this->options, $options);
        
        // Initialize Item Object
        $item = new ItemModel;
        
        // Fetch Shared Item Data
        F3::sqlBind($this->query['item'], array("item" => $identifier));
        $shared = F3::get('DB.result.0');
        
        // Presumably, no item data found
        if (empty($shared)) {
            return false;
        }
        
        // Assign Class Attributes
        foreach($shared as $key => $attribute) {
            $item->$key = $attribute;
        }
        
         // Fetch and translate item properties
        if ($this->options['verbose']) {
            switch ($item->rarity) {
                case "normal" :
                
                    // Properties
                    $item->properties['normal'] = F3::sqlBind($this->query['properties'], array("item" => $item->name));
                    
                    // Variants
                    $item->variants = F3::sqlBind($this->query['variants'], array("item" => $item->name));
                    
                    break;
                
                case "unique" : 
                
                    // Properties
                    $item->properties['normal'] = F3::sqlBind($this->query['properties'], array("item" => $item->class));
                    $item->properties['magic']  = F3::sqlBind($this->query['properties'], array("item" => $item->name));
                    
                    // Similar
                    $item->similar = F3::sqlBind($this->query['similar'], array("item" => $item->name, "division" => $item->division, "level" => $item->level, "limit" => $this->options['count']));
                    
                    break;
                
                case "set" :

                    // Family
                    F3::sqlBind($this->query['family'], array("item" => $item->name));
                    $item->family = F3::get('DB.result.0.set_family');
                    
                    // Siblings
                    $item->siblings = F3::sqlBind($this->query['siblings'], array("family" => $item->family));
                    
                    // Properties
                    $item->properties['normal'] = F3::sqlBind($this->query['properties'], array("item" => $item->class));
                    $item->properties['magic']  = F3::sqlBind($this->query['properties'], array("item" => $item->name));
                    $item->properties['set']    = F3::sqlBind($this->query['properties_set'], array("item" => $item->name));
                    $item->properties['family'] = F3::sqlBind($this->query['properties_family'], array("family" => $item->family));
                    
                    // Similar
                    $item->similar = F3::sqlBind($this->query['similar'], array("item" => $item->name, "division" => $item->division, "level" => $item->level, "limit" => $this->options['count']));
                    
                    break;
            }
            
            // Translate Item Properties
            foreach($item->properties as $key => $property) {
                
                if (!empty($property)) {
                    foreach($property as $rowKey => $row) {
                    
                        if ($row['min'] == $row['max']) {
                            $item->properties[$key][$rowKey]['translation'] = $this->translate($row['translation'], $row['parameter'], $row['min'], $row['max']);
                        } else {
                            $item->properties[$key][$rowKey]['translation'] = $this->translate($row['translation_varies'], $row['parameter'], $row['min'], $row['max']);
                        }
                        
                        // Remove unused translation_varies
                        unset($item->properties[$key][$rowKey]['translation_varies']);
                    }
                }
                
            }
        }
        
        // Return JSON string
        return (json_encode($item));
    }
    
    /**
        Translate item properties
            @return $translation string
            @param $translation string
            @param $parameter string
            @param $minimum int
            @param $maximum int
            @protected
    **/
    protected function translate($translation, $parameter, $minimum, $maximum) {
        $translation = str_replace("@param", $parameter, $translation);
        $translation = str_replace("@min", $minimum, $translation);
        $translation = str_replace("@max", $maximum, $translation);
        
        return $translation;
    }
    
    /**
        Fetch All Item and Basic Properties
            @public
    **/
    public function all() {
        return F3::sqlBind($this->query['all']);
    }
    
    /**
        Fetch Item Types That Have Relevant Items Attached to Them
            @public
    **/
    public function types() {
        return F3::sqlBind($this->query['types']);
    }
}