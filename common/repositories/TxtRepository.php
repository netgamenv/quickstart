<?php

namespace common\repositories;

// Repository base for csv-based repository
class TxtRepository {
    private $data = [];
    private $handle;
    private $dataFile = '';
    private $columnsCount = 0;

    public function __construct(string $dataFile, int $columnsCount)
    {
        $this->dataFile = __DIR__ . "/../../data/" . $dataFile;
        $this->columnsCount = $columnsCount;
        $this->_open();
        $this->_load();
    }

    public function __destruct()
    {
        $this->_close();
    }

    private function _open()
    {
        $this->handle = fopen($this->dataFile, "r+");
        flock($this->handle, LOCK_EX);
    }

    private function _load(){
        $this->data = [];
        while (($data = fgetcsv($this->handle, 1000, ";")) !== FALSE) {
            if( count($data) != $this->columnsCount) {
                continue;
            }
            $id = $data[0];
            $this->data[$id] = $data;
        }
    }

    private function _save(){
        fseek($this->handle, 0);
        foreach( $this->data as $item ) {
            $line = implode(";",$item) . "\n";
            fwrite($this->handle, $line);
        }
    }

    private function _close(){
        fclose($this->handle);
    }

    protected function _getItems(){
        return $this->data;
    }

    /**
     * @param $itemFilter
     * @param int $column
     * @return array|null
     */
    protected function _getItem( $itemFilter, int $column = 1)
    {
        if( 1 == $column ) {
            return $this->data[$itemFilter] ?? null;
        }

        foreach( $this->data as $itemId => $itemData) {
            $columnValue = $itemData[$column-1];
            if( $itemFilter == $columnValue ) {
                return $itemData;
            }
        }

        return null;
    }

    protected function _getMaxId(){
        $itemId = 0;
        foreach( $this->data as $curItemId => $itemData ) {
            if( $curItemId > $itemId) {
                $itemId = $curItemId;
            }
        }
        return $itemId + 1;
    }

    protected function _setItem(int $itemId, array $data)
    {
        $this->data[$itemId] = $data;
        $this->_save();
    }


}