<?php
/**
 * Edit a item.
 *
 * This file is part of 'Kitto_Kitto_Kitto'.
 *
 * 'Kitto_Kitto_Kitto' is free software; you can redistribute
 * it and/or modify it under the terms of the GNU
 * General Public License as published by the Free
 * Software Foundation; either version 3 of the License,
 * or (at your option) any later version.
 * 
 * 'Kitto_Kitto_Kitto' is distributed in the hope that it will
 * be useful, but WITHOUT ANY WARRANTY; without even the
 * implied warranty of MERCHANTABILITY or FITNESS FOR A
 * PARTICULAR PURPOSE.  See the GNU General Public
 * License for more details.
 * 
 * You should have received a copy of the GNU General
 * Public License along with 'Kitto_Kitto_Kitto'; if not,
 * write to the Free Software Foundation, Inc., 51
 * Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @author Nicholas 'Owl' Evans <owlmanatt@gmail.com>
 * @copyright Nicolas Evans, 2007
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GPLv3
 * @package Kitto_Kitto_Kitto
 * @subpackage Items 
 * @version 1.0.0
 **/

$ERRORS = array();
$item_id = stripinput($_REQUEST['item']['id']);
$item = new ItemType($db);
$item = $item->findOneByItemTypeId($item_id);

if($item == null)
{
    $ERRORS[] = 'Invalid item specified.';
}

if(sizeof($ERRORS) > 0)
{
    draw_errors($ERRORS);
}
else
{
    switch($_REQUEST['state'])
    {
        default:
        {
            if($item != null)
            {
                $ITEM = array(
                    'id' => $item->getItemTypeId(),
                    'type' => $item->getClassDescr(),
                    'name' => $item->getItemName(),
                    'image' => $item->getItemImage(),
                    'description' => $item->getItemDescr(),
                );
            } // end edit mode

            $fields = $item->listAttributes();
            
            $renderer->assign('extra_fields',$fields);
            $renderer->assign('item',$ITEM);
            $renderer->display('admin/items/edit.tpl');

            break;
        } // end default

        case 'save':
        {
            $ITEM = array(
                'name' => trim(stripinput($_POST['item']['name'])),
                'image' => trim(stripinput($_POST['item']['image'])),
                'description' => trim(stripinput($_POST['item']['description'])),
            );
            
            // If the group could not be loaded, start making a new one.
            if($item == null)
            {
                $item = new ItemType($db);
            }
            
            if($ITEM['name'] == null)
            {
                $ERRORS[] = 'No name specified.';
            }
            elseif(strlen($ITEM['name']) > 50)
            {
                $ERRORS[] = 'There is a maxlength=50 on that field for a reason.';
            }

            if($ITEM['image'] == null)
            {
                $ERRORS[] = 'No image specified.';
            }
            elseif(strlen($ITEM['image']) > 200)
            {
                $ERRORS[] = 'There is a maxlength=200 on that field for a reason.';
            }

            if($ITEM['description'] == null)
            {
                $ERRORS[] = 'No description specified.';
            }

            if(sizeof($ERRORS) > 0)
            {
                draw_errors($ERRORS);
            }
            else
            {
                $item->setItemName($ITEM['name']);
                $item->setItemImage($ITEM['image']);
                $item->setItemDescr($ITEM['description']);
                $item->save();

                $_SESSION['item_notice'] = "You have saved <strong>{$item->getItemName()}</strong>.";
                redirect('admin-items');
            } // end no errors

            break;
        } // end save
    } // end state switch
} // end no errors
?>
