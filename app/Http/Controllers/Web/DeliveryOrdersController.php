<?php

namespace App\Http\Controllers\Web;

use App\CityCatalog;
use App\CountyCatalog;
use App\DeliveryOrder;
use App\UserRole;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;

class DeliveryOrdersController extends Controller
{
    public function index()
    {
        return view('deliveryOrders.index');
    }

    public function pList()
    {
        $params = Input::all();

        if(Auth::user()->hasRole(UserRole::SUPERVISOR))
        {
            Auth::user()->initBranch();

            $params['branchId'] = 1;
        }

        $deliveryOrders = DeliveryOrder::getCollection($params)->get();

        return view('deliveryOrders.list', [
            'deliveryOrders' => $deliveryOrders
        ]);
    }

    public function editForm()
    {
        $deliveryOrderId = Input::get('deliveryOrderId');

        $deliveryOrder = new DeliveryOrder();
        if($deliveryOrderId)
        {
            $deliveryOrder = DeliveryOrder::find($deliveryOrderId);

//            if($deliveryOrder)
//            {
//                $deliveryOrder->initCity();
//                $deliveryOrder->initCounty();
//            }
        }

        $cities = CityCatalog::getCollection(['actual' => 1])->get();
        $counties = CountyCatalog::getCollection(['actual' => 1])->get();

        return view('deliveryOrders.edit', [
            'deliveryOrder' => $deliveryOrder,
            'cities' => $cities,
            'counties' => $counties,
        ]);
    }

    public function submitForm()
    {
        $deliveryOrderData = Input::all();

        $successMessage = $deliveryOrderData['id'] ? 'Заявка была успешно обновлена.' : 'Заявка была успешно добавлена.';

        $deliveryOrder = new DeliveryOrder();

        $deliveryOrder->setDataFromArray($deliveryOrderData);

        $errors = $deliveryOrder->validateData();

        if(!$errors)
        {
            if($deliveryOrder->submitData())
            {
                return response()->json([
                    'success' => true,
                    'message' => $successMessage
                ]);
            }
        }
        else
        {
            return response()->json([
                'success' => false,
                'message' => $errors[0]
            ]);
        }
    }

    public function delete()
    {
        $deliveryOrderId = Input::get('deliveryOrderId');

        if($deliveryOrderId)
        {
            $deliveryOrder = DeliveryOrder::find($deliveryOrderId);
            if($deliveryOrder)
            {
                $deliveryOrder->status = Status::DELETED;

                if($deliveryOrder->save())
                {
                    return response()->json([
                        'success' => true,
                        'message' => 'Заявка была успешно удалена.'
                    ]);
                }
                else
                {
                    return response()->json([
                        'success' => false,
                        'message' => 'Ошибка! Произошла ошибка при удалении заявки.'
                    ]);
                }
            }
            else
            {
                return response()->json([
                    'success' => false,
                    'message' => 'Ошибка! Не найдена заявка с указанным идентификатором.'
                ]);
            }
        }
        else
        {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка! Не передан идентификатор заявки.'
            ]);
        }
    }
}
