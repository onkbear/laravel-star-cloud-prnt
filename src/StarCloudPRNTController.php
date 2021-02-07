<?php

namespace Onkbear\StarCloudPRNT;

use Illuminate\Http\Request;

class StarCloudPRNTController
{
    /**
     * Polling the server request. (POST)
     * https://www.star-m.jp/products/s_print/CloudPRNTSDK/Documentation/en/developerguide/pollingserver/post_overview.html
     */
    public function handlePoll(Request $request)
    {
        $pollResponse = [
            // Set jobReady to false by default, this is enough to provide the minimum cloudprnt response.
            'jobReady' => false
        ];

        $printer = new Printer($request->printerMAC);

        if ($request->has('clientAction') && $request->clientAction) {
            // Client action responses received, meaning that the cloudPRNT device has responded to a request from the server.
            // This server will request print/paper size and the client type/version when needed.

            // Parse results.
            foreach ($request->clientAction as $clientAction) {
                $key = $clientAction['request'];
                $value = $clientAction['result'];

                if ($key === 'PageInfo') {
                    $printer->paper_width = intval($value['paperWidth']);
                    $printer->print_width = intval($value['printWidth']);
                    $printer->dot_width = intval($value['printWidth']) * intval($value['horizontalResolution']);
                } elseif ($key === 'GetPollInterval') {
                    $printer->get_poll_interval = $value;
                } elseif ($key === 'Encodings') {
                    $printer->encodings = explode(' ', str_replace(';', '', $value));
                } elseif ($key === 'ClientType') {
                    $printer->client_type = $value;
                } elseif ($key === 'ClientVersion') {
                    $printer->client_version = $value;
                }
            }

            $printer->save();

        } else {
            // Obtain printer device info, to see if printer was offline for a while.
            if (! $printer->isOnline()) {
                // If the device width is not stored in the database, then use a client action to request it, and other device infor at the same time.
                $pollResponse['clientAction'] = [
                    [ 'request' => 'PageInfo' ],
                    [ 'request' => 'GetPollInterval' ],
                    [ 'request' => 'Encodings' ],
                    [ 'request' => 'ClientType' ],
                    [ 'request' => 'ClientVersion' ],
                ];
            } else {
                // No client action is needed, so check the database to see if a ticket has been requested.
                if (! $printer->printing && $printer->getQueueCount()) {

                    // A ticket has been requested, so let the device know that printing is needed.
                    $pollResponse['jobReady'] = true;
    
                    // The device will select one format from this list, based on it's internal compatibility and capabilities.
                    $pollResponse['mediaTypes'] = array_keys(config('star-cloud-prnt.supported_encordings'));
                }
            }
        }

        // Save status and update timestamp.
        $printer->setDeviceStatus($request->statusCode);

        return response()->json($pollResponse);
    }

    /**
     * Print job request. (GET)
     * https://www.star-m.jp/products/s_print/CloudPRNTSDK/Documentation/en/developerguide/printjobrequests/get_overview.html
     */
    public function handleJob(Request $request)
    {
        if (! $request->has('mac') || ! $request->has('type')) {
            return response(404);
        }

        // Unsupported media type ?
        if (! array_key_exists($request->type, config('star-cloud-prnt.supported_encordings'))) {
            return response(415);
        }

        $printer = new Printer($request->mac);
        $job = $printer->getOldestJob();

        if (empty($job)) {
            return response(404);
        }

        $contents = $job['contents'];

        return response($contents)->header('Content-Type', $job['type']);
    }

    /**
     * Print job confirmation request. (DELETE)
     * https://www.star-m.jp/products/s_print/CloudPRNTSDK/Documentation/en/developerguide/printjobconfirmation/delete_overview.html
     */
    public function handleDelete(Request $request)
    {
        if (! $request->has('mac') || ! $request->has('code')) {
            return response(404);
        }

        $printer = new Printer($request->mac);

        if ($printer && (substr($request->code, 0, 1) === '2')) {
            $printer->popJob();
        }

        return response(200);
    }
}
