<?php

namespace common\decorators;

use common\exceptions\InvalidRequestGameException;
use common\facades\Logging;

class JsonRequestHandlerDecorator {


    private $defaultRequestData;
    private $requestDataRaw;
    private $requestData;
    private $charsetDisabled = false;

    public function __construct($defaultRequestData = null)
    {
        $this->defaultRequestData = $defaultRequestData;
    }


    private function _response(array $responseData) {
        $message = "Request was: ";
        if( $this->requestData ) {
            $message .= json_encode($this->requestData, JSON_PRETTY_PRINT) ;
        } else {
            $message .= $this->requestDataRaw;
        }
        $message .= "\nResponse is: " . json_encode($responseData, JSON_PRETTY_PRINT);
        Logging::info( $message );

        if( isset($responseData['HTTP_STATUS_CODE'])) {
            http_response_code($responseData['HTTP_STATUS_CODE']);
            unset($responseData['HTTP_STATUS_CODE']);
        }
        if( $this->charsetDisabled ) {
            header('Content-Type: application/json');
        } else {
            header('Content-Type: application/json; charset="UTF-8"');
        }


        // Protection against spaces and tabs at beginning of files
        $content = ob_get_clean();
        $content = trim($content);

        $response = $content . json_encode($responseData);
        echo $response;
    }

    public function handle($callable, $defaultResponse = []) {

        $this->requestDataRaw = file_get_contents("php://input");
        $this->requestData = json_decode($this->requestDataRaw, true);
        if( !$this->requestData ) {
            if( is_null($this->defaultRequestData)) {
                $this->error("Failed to decode request: '{$this->requestDataRaw}'");
                if (!$defaultResponse) {
                    throw new InvalidRequestGameException("Failed to decode request");
                }

                $this->_response($defaultResponse);
                return;
            } else {
                $this->requestData = $this->defaultRequestData;
            }
        }

        try {
            $responseData = $callable($this->requestData, $this->requestDataRaw);

            $this->_response($responseData);
        } catch(\Throwable $e){
            Logging::error("Failed to process request: " . $e->getMessage());
            if( !$defaultResponse ) {
                throw new InvalidRequestGameException("Internal error");
            }

            $this->_response($defaultResponse);
            return;
        }
    }

}
