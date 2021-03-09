<?php
namespace app\models;
/**
 * Created by PhpStorm.
 * @author Andrei G. Pastushenko
 * Date: 08.10.2017
 * Time: 22:29
 */
/**
 * Class CellsModel
 * @package app\models
 */
class CellsModel extends AppModel
{
    /**
     * @var string
     */
    public string $table = 'stock';

    public function insertNewCellToBufferPageFromSocket($data)
    {
        $this->socket->sendPublicMessage('cells_buffer', 'insert_new', $data);
    }

    public function deleteDivCellToBufferPageFromSocket($id)
    {
        $this->socket->sendPublicMessage('cells_buffer', 'delete_div_cell', $id);
    }

    public function cellUpdateToBufferPageFromSocket($new_cell)
    {
        $this->socket->sendPublicMessage('cells_buffer', 'cell_update', $new_cell);
    }
}