<?
class CleverisionHandlers {
    function OnServicesFormSend(&$arFields)
    {
        try {
            $serviceIBlockId = 2;
            $serviceFormIBlockId = 10;
            $formServiceListPropId = 26;

            if ($arFields["IBLOCK_ID"] != $serviceFormIBlockId) {
                return;
            }

            if (is_string($arFields["PREVIEW_TEXT"])  && strlen($arFields["PREVIEW_TEXT"])) {
                $arRawServices = explode(",", trim($arFields["PREVIEW_TEXT"]));
                array_walk($arRawServices, function(&$value) {
                    $value = filter_var($value, FILTER_VALIDATE_INT);
                });
                $arRawServices = array_filter($arRawServices, function($value){
                    return $value > 0;
                });
                $arRawServices = array_unique($arRawServices);

                if (count($arRawServices)) {
                    $arCheckedId = array();

                    $obElement = new CIBlockElement();
                    $obElementList = $obElement->GetList(
                        array(),
                        array(
                            "IBLOCK_ID" => $serviceIBlockId,
                            "ID" => $arRawServices,
                        ),
                        false,
                        false,
                        array("ID",)
                    );
                    while ($arElement = $obElementList->Fetch()) {
                        $arCheckedId[] = $arElement["ID"];
                    }

                    if (count($arCheckedId) <= 0) {
                        $arRawServices = array();
                    } else {
                        $arRawServices = array_filter($arRawServices, function($value) use ($arCheckedId){
                            return in_array($value, $arCheckedId);
                        });
                    }

                }

                foreach ($arRawServices as $serviceId) {
                    $arFields["PROPERTY_VALUES"][$formServiceListPropId][] = $serviceId;
                }
            }
        } catch(Throwable $e) {}
    }
}

AddEventHandler("iblock", "OnBeforeIBlockElementAdd", Array("CleverisionHandlers", "OnServicesFormSend"));
