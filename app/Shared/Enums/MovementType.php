<?php

namespace App\Shared\Enums;

enum MovementType: string
{
    case GOODS_RECEIPT = 'goods_receipt';
    case GOODS_ISSUE = 'goods_issue';
    case MATERIAL_RETURN = 'material_return';
    case PRODUCTION_OUTPUT = 'production_output';
    case FINISHED_GOODS_RECEIPT = 'finished_goods_receipt';
    case STOCK_TRANSFER_OUT = 'stock_transfer_out';
    case STOCK_TRANSFER_IN = 'stock_transfer_in';
    case STOCK_ADJUSTMENT = 'stock_adjustment';
    case STOCK_DAMAGE = 'stock_damage';
    case STOCK_SCRAP = 'stock_scrap';
    case CUSTOMER_RETURN = 'customer_return';
    case SUPPLIER_RETURN = 'supplier_return';
}