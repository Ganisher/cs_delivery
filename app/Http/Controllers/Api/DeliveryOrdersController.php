<?php

namespace App\Http\Controllers\Api;

use App\DeliveryOrder;
use App\DeliveryOrderStatus;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;

class DeliveryOrdersController extends Controller
{
    public function index()
    {
        return response()->json([
            'status' => 200,
            'data' => [
                'message' => 'Hello, World!'
            ]
        ]);
    }

    public function submitForm()
    {
        $deliveryOrderData = Input::all();

        $successMessage = isset($deliveryOrderData['id']) ? 'Данные заявки были успешно обновлены.' : 'Заявка была успешно добавлена.';

        $deliveryOrder = new DeliveryOrder();

        $deliveryOrder->setDataFromArray($deliveryOrderData);

        $errors = $deliveryOrder->validateData();

        if(!$errors)
        {
            if($deliveryOrder->submitData())
            {
                return response()->json([
                    'success' => true,
                    'status' => 200,
                    'message' => $successMessage
                ]);
            }
        }
        else
        {
            return response()->json([
                'success' => false,
                'status' => 500,
                'message' => $errors[0]
            ]);
        }
    }

    public function getFiles()
    {
        $orderID = Input::get('orderID');

        if($orderID)
        {
            $order = DeliveryOrder::find($orderID);

            if($order)
            {
                $files = $order->getMedia('orderClientDocs');

                if(count($files))
                {
                    $output = [];
                    foreach ($files as $file)
                    {
                        $path = public_path($file->getUrl());
                        $type = pathinfo($path, PATHINFO_EXTENSION);
                        $data = file_get_contents($path);
                        $base64 = 'data:application/' . $type . ';base64,' . base64_encode($data);
                        $output[] = [
                            'type' => $file->getCustomProperty('type'),
                            'filename' => $file->file_name,
                            'mime' => $file->mime_type,
                            'content' => $base64
                        ];
                    }

                    return response()->json([
                        'success' => true,
                        'status' => 200,
                        'message' => $output
                    ]);
                }
                else
                {
                    return response()->json([
                        'success' => false,
                        'status' => 500,
                        'message' => 'Не найдены файлы заявки.'
                    ]);
                }
            }
            else
            {
                return response()->json([
                    'success' => false,
                    'status' => 500,
                    'message' => 'Не найдена заявка с указанным идентификатором.'
                ]);
            }
        }
        else
        {
            return response()->json([
                'success' => false,
                'status' => 500,
                'message' => 'Не передан идентификатор заявки.'
            ]);
        }

    }

    public function setSDStatusReceived()
    {
        $orderID = Input::get('orderID');

        if($orderID)
        {
            $order = DeliveryOrder::find($orderID);

            if($order)
            {
                $order->springDocStatus = DeliveryOrderStatus::RECEIVED;

                if($order->save())
                {
                    return response()->json([
                        'success' => false,
                        'status' => 200,
                        'message' => 'Статус заявки была успешно обновлена.'
                    ]);
                }
            }
            else
            {
                return response()->json([
                    'success' => false,
                    'status' => 500,
                    'message' => 'Не найдена заявка с указанным идентификатором.'
                ]);
            }
        }
        else
        {
            return response()->json([
                'success' => false,
                'status' => 500,
                'message' => 'Не передан идентификатор заявки.'
            ]);
        }
    }

    public function setSDStatusVerified()
    {
        $orderID = Input::get('orderID');

        if($orderID)
        {
            $order = DeliveryOrder::find($orderID);

            if($order)
            {
                $order->springDocStatus = DeliveryOrderStatus::VERIFIED;

                if($order->save())
                {
                    return response()->json([
                        'success' => false,
                        'status' => 200,
                        'message' => 'Статус заявки была успешно обновлена.'
                    ]);
                }
            }
            else
            {
                return response()->json([
                    'success' => false,
                    'status' => 500,
                    'message' => 'Не найдена заявка с указанным идентификатором.'
                ]);
            }
        }
        else
        {
            return response()->json([
                'success' => false,
                'status' => 500,
                'message' => 'Не передан идентификатор заявки.'
            ]);
        }
    }
}
