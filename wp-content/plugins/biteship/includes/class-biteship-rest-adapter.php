<?php

class Biteship_Rest_Adapter
{
    public $timeout;
    public $license_key;
    public function __construct($license_key)
    {
        $this->license_key = $license_key;
        $this->base_url = "https://api.biteship.com";
        $this->list_province_code = [
            "IDNP1" => "Bali",
            "IDNP2" => "Bangka Belitung",
            "IDNP3" => "Banten",
            "IDNP4" => "Bengkulu",
            "IDNP5" => "DI Yogyakarta",
            "IDNP6" => "DKI Jakarta",
            "IDNP7" => "Gorontalo",
            "IDNP8" => "Jambi",
            "IDNP9" => "Jawa Barat",
            "IDNP10" => "Jawa Tengah",
            "IDNP11" => "Jawa Timur",
            "IDNP12" => "Kalimantan Barat",
            "IDNP13" => "Kalimantan Selatan",
            "IDNP14" => "Kalimantan Tengah",
            "IDNP15" => "Kalimantan Timur",
            "IDNP16" => "Kalimantan Utara",
            "IDNP17" => "Kepulauan Riau",
            "IDNP18" => "Lampung",
            "IDNP19" => "Maluku",
            "IDNP20" => "Maluku Utara",
            "IDNP21" => "Nanggroe Aceh Darussalam (NAD)",
            "IDNP22" => "Nusa Tenggara Barat (NTB)",
            "IDNP23" => "Nusa Tenggara Timur (NTT)",
            "IDNP24" => "Papua",
            "IDNP25" => "Papua Barat",
            "IDNP26" => "Riau",
            "IDNP27" => "Sulawesi Barat",
            "IDNP28" => "Sulawesi Selatan",
            "IDNP29" => "Sulawesi Tengah",
            "IDNP30" => "Sulawesi Tenggara",
            "IDNP31" => "Sulawesi Utara",
            "IDNP32" => "Sumatera Barat",
            "IDNP33" => "Sumatera Selatan",
            "IDNP34" => "Sumatera Utara",
        ];
        $this->timeout = 50;
    }

    public function http_get($url)
    {
        $args = [
            "headers" => [
                "source" => "Biteship-Wordpress",
                "authorization" => "Bearer " . $this->license_key,
            ],
            "timeout" => $this->timeout,
        ];

        $response = wp_remote_get($url, $args);
        if (is_wp_error($response)) {
            return __("Something went wrong, please try again", "biteship");
        }

        return json_decode(wp_remote_retrieve_body($response), true);
    }

    public function http_post($url, $body)
    {
        $args = [
            "body" => json_encode($body),
            "headers" => [
                "source" => "Biteship-Wordpress",
                "authorization" => "Bearer " . $this->license_key,
                "content-type" => "application/json",
            ],
            "timeout" => $this->timeout,
        ];

        $response = wp_remote_post($url, $args);
        if (is_wp_error($response)) {
            return __("Something went wrong, please try again", "biteship");
        }

        return json_decode(wp_remote_retrieve_body($response), true);
    }

    private function http_delete($url)
    {
        $args = [
            "method" => "DELETE",
            "headers" => [
                "authorization" => "Bearer " . $this->license_key,
            ],
            "timeout" => $this->timeout,
        ];

        $response = wp_remote_request($url, $args);
        if (is_wp_error($response)) {
            return __("Something went wrong, please try again", "biteship");
        }

        return json_decode(wp_remote_retrieve_body($response), true);
    }

    private function get_coordinate_from_location($fulladdress)
    {
        $destination_latitude = null;
        $destination_longitude = null;
        $res = $this->getGmapAPI();
        $gmap_api_key = $res["success"] ? $this->decGmapAPI($res["data"]) : "";
        $responseGmap = json_decode(file_get_contents("https://maps.googleapis.com/maps/api/geocode/json?address=$fulladdress&key=$gmap_api_key"), true);
        if (count($responseGmap["results"]) > 0) {
            $destination_latitude = $responseGmap["results"][0]["geometry"]["location"]["lat"];
            $destination_longitude = $responseGmap["results"][0]["geometry"]["location"]["lng"];
        }
        return [$destination_latitude, $destination_longitude];
    }

    private function get_multiple_origin($multiple_addresses)
    {
        $multiple_address = null;
        if (isset($_POST["post_data"])) {
            parse_str($_POST["post_data"], $array);
            $multi_origin = $array["billing_biteship_multi_origins"];
            $shipping_biteship_multi_origins = $array["shipping_biteship_multi_origins"];
            if (strlen($multi_origin) === 0 && strlen($shipping_biteship_multi_origins) === 0) {
                return $multiple_address;
            }
            if (strlen($shipping_biteship_multi_origins) > 0) {
                $multi_origin = $shipping_biteship_multi_origins;
            }
            foreach ($multiple_addresses as $multiple_address) {
                if ($multiple_address["id"] === $multi_origin && strlen($multi_origin) > 0) {
                    break;
                }
            }
        }
        return $multiple_address;
    }

    // TODO: return object instead of multiple types result
    public function get_pricing($query)
    {
        $requested_services = $query["requested_services"];
        $url = $this->base_url . "/v1/rates/couriers?channel=woocommerce";
        $origin_latitude = null;
        $origin_longitude = null;
        $destination_latitude = null;
        $destination_longitude = null;
        if ($query["origin_latitude"] != "" && $query["origin_longitude"] != "") {
            $origin_latitude = $query["origin_latitude"];
            $origin_longitude = $query["origin_longitude"];
        }
        if ($query["destination_latitude"] != "" && $query["destination_longitude"] != "") {
            $destination_latitude = $query["destination_latitude"];
            $destination_longitude = $query["destination_longitude"];
        }

        // if your multi origin active
        $get_multiple_address = null;
        $multiple_origins_isactive = $query["multiple_origins_isactive"];
        if ($multiple_origins_isactive) {
            $get_multiple_address = $this->get_multiple_origin($query["multiple_addresses"]);
            if ($get_multiple_address !== null) {
                $query["origin_postal_code"] = $get_multiple_address["zipcode"];
                list($origin_latitude, $origin_longitude) = explode(",", $get_multiple_address["position"]);
            }
        }

        if ($destination_latitude == null && $destination_longitude == null) {
            if (isset($_POST["post_data"])) {
                parse_str($_POST["post_data"], $param);
                if (isset($param["billing_biteship_location_coordinate"]) && isset($param["shipping_biteship_location_coordinate"])) {
                    $location_coordinate = urldecode($param["billing_biteship_location_coordinate"]);
                    $shipping_location_coordinate = urldecode($param["shipping_biteship_location_coordinate"]);
                    if (strlen($shipping_location_coordinate) > 0) {
                        $location_coordinate = $shipping_location_coordinate;
                    }
                    list($destination_latitude, $destination_longitude) = explode(",", $location_coordinate);
                }
            }
        }

        $formatted_requested_services = [];
        foreach ($requested_services as $courierService) {
            $formatted_requested_services[] = str_replace('/', '_', $courierService);
        }
        $body = [
            "origin_latitude" => $origin_latitude,
            "origin_longitude" => $origin_longitude,
            "origin_postal_code" => $query["origin_postal_code"],
            "destination_latitude" => $destination_latitude,
            "destination_longitude" => $destination_longitude,
            "destination_postal_code" => $query["destination_postal_code"],
            "couriers" => implode(",", $formatted_requested_services),
            "items" => [],
        ];

        foreach ($query["items"] as $item) {
            array_push($body["items"], [
                "name" => $item["name"],
                "sku" => $item["sku"],
                "length" => $item["length"],
                "width" => $item["width"],
                "height" => $item["height"],
                "weight" => $item["weight"],
                "quantity" => $item["quantity"],
                "value" => $item["value"],
            ]);
        }

        //cache request.
        session_start();
        $use_cache = false;
        $hash_courier = md5(json_encode($body));
        if (isset($_SESSION[$hash_courier]) && strlen($_SESSION[$hash_courier]) > 0) {
            $use_cache = true;
            $data = json_decode($_SESSION[$hash_courier], 1);
        } else {
            $data = $this->http_post($url, $body);
            $_SESSION[$hash_courier] = json_encode($data);
        }

        if (!is_array($data)) {
            return $data;
        }

        if (isset($data["error"]) && strlen($data["error"]) > 0) {
            return $data["error"];
        }

        $result = [];
        foreach ($data["pricing"] as $pricing) {
            $service_code = $pricing["courier_code"] . "/" . $pricing["courier_service_code"];
            $company = $pricing["courier_name"];
            $service_name = $pricing["courier_service_name"];
            $duration = $pricing["duration"];
            $price = $pricing["price"];

            // TODO: remove unnecessary cod flag in id, use data from metadata instead
            $id = $pricing["available_for_cash_on_delivery"] ? "cod/" . $service_code : $service_code;
            array_push($result, [
                "id" => $id,
                "label" => $company . " - " . $service_name . " (" . $duration . ") ",
                "cost" => $price,
                "meta_data" => [
                    "courier_code" => $pricing["courier_code"],
                    "courier_service_code" => $pricing["courier_service_code"],
                    "is_cod_available" => $pricing["available_for_cash_on_delivery"],
                ],
            ]);
        }
        return $result;
    }

    // TODO: return object instead of multiple types result
    public function get_couriers()
    {
        $url = $this->base_url . "/v1/couriers";
        $data = $this->http_get($url);
        if (!is_array($data)) {
            return $data;
        }

        if (!$data["success"]) {
            return __("Please get api key first.", "biteship");
        }

        $couriers_raw = $data["couriers"];

        $couriers = [];
        foreach ($couriers_raw as $raw) {
            $courier_code = $raw["courier_code"];
            if (!array_key_exists($courier_code, $couriers)) {
                $couriers[$courier_code] = [
                    "name" => $raw["courier_name"],
                    "code" => $raw["courier_code"],
                    "services" => [],
                ];
            }
            array_push($couriers[$courier_code]["services"], [
                "name" => $raw["courier_service_name"],
                "code" => $raw["courier_service_code"],
                "tier" => $raw["tier"],
                "description" => $raw["description"],
                "service_type" => $raw["service_type"],
                "shipping_type" => $raw["shipping_type"],
                "shipment_duration_range" => $raw["shipment_duration_range"],
                "shipment_duration_unit" => $raw["shipment_duration_unit"],
            ]);
        }

        return $couriers;
    }

    public function create_order($order)
    {
        $url = $this->base_url . "/v1/woocommerce/orders";

        $items = [];
        foreach ($order["items"] as $item) {
            array_push($items, [
                "id" => $item["id"],
                "name" => $item["name"],
                "image" => $item["image"],
                "sku" => $item["sku"],
                "description" => $item["description"],
                "value" => $item["value"],
                "quantity" => $item["quantity"],
                "height" => $item["height"],
                "length" => $item["length"],
                "weight" => $item["weight"],
                "width" => $item["width"],
            ]);
        }

        $body = [
            "shipper_contact_name" => $order["shipper_contact_name"],
            "shipper_contact_phone" => $order["shipper_contact_phone"],
            "shipper_contact_email" => $order["shipper_contact_email"],
            "shipper_organization" => $order["shipper_organization"],
            "origin_contact_name" => $order["origin_contact_name"],
            "origin_contact_phone" => $order["origin_contact_phone"],
            "origin_address" => $order["origin_address"],
            "origin_note" => $order["origin_note"],
            "origin_postal_code" => $order["origin_postal_code"],
            "origin_coordinate" => [
                "latitude" => $order["origin_coordinate_latitude"],
                "longitude" => $order["origin_coordinate_longitude"],
            ],
            "destination_contact_name" => $order["destination_contact_name"],
            "destination_contact_phone" => $order["destination_contact_phone"],
            "destination_contact_email" => $order["destination_contact_email"],
            "destination_address" => $order["destination_address"],
            "destination_postal_code" => $order["destination_postal_code"],
            "destination_note" => $order["destination_note"],
            "destination_coordinate" => [
                "latitude" => $order["destination_coordinate_latitude"],
                "longitude" => $order["destination_coordinate_longitude"],
            ],
            "courier_company" => $order["courier_company"],
            "courier_type" => $order["courier_type"],
            "delivery_type" => $order["delivery_type"],
            "delivery_date" => $order["delivery_date"],
            "delivery_time" => $order["delivery_time"],
            "order_note" => $order["order_note"],
            "items" => $items,
        ];

        if ($order["destination_cash_on_delivery"]) {
            $body["destination_cash_on_delivery"] = $order["destination_cash_on_delivery"];
        }

        if ($order["courier_insurance"]) {
            $body["courier_insurance"] = $order["courier_insurance"];
        }

        if ($order["webhooks"]) {
            $body["webhooks"] = $order["webhooks"];
        }

        $data = $this->http_post($url, $body);
        if (!is_array($data)) {
            return [
                "success" => false,
                "error" => "There's something wrong, please try again.",
            ];
        }

        if (!$data["success"]) {
            return [
                "success" => false,
                "error" => $this->errorMaping($data["error"]),
            ];
        }

        return [
            "success" => true,
            "data" => [
                "order_id" => $data["id"],
                "status" => $data["status"],
                "note" => isset($data["note"]) ? $data["note"] : "",
                "updated_at" => isset($data["updated_at"]) ? $data["updated_at"] : "",
                "waybill_id" => isset($data["courier"]["waybill_id"]) ? $data["courier"]["waybill_id"] : "",
            ],
        ];
    }

    public function bulk_create_order($shipper, $origin, $orders)
    {
        $url = $this->base_url . "/v1/woocommerce/bulk_orders/create";

        $destinations = [];
        foreach ($orders as $order) {
            $items = [];
            foreach ($order["items"] as $item) {
                array_push($items, [
                    "id" => $item["id"],
                    "name" => $item["name"],
                    "image" => $item["image"],
                    "sku" => $item["sku"],
                    "description" => $item["description"],
                    "value" => $item["value"],
                    "quantity" => $item["quantity"],
                    "height" => $item["height"],
                    "length" => $item["length"],
                    "weight" => $item["weight"],
                    "width" => $item["width"],
                ]);
            }

            $destination = [
                "contact_name" => $order["destination_contact_name"],
                "contact_phone" => $order["destination_contact_phone"],
                "address" => $order["destination_address"],
                "note" => $order["destination_note"],
                "postal_code" => $order["destination_postal_code"],
                "coordinate" => [
                    "latitude" => $order["destination_coordinate_latitude"],
                    "longitude" => $order["destination_coordinate_longitude"],
                ],
                "items" => $items,
                "delivery_date" => $order["delivery_date"],
                "delivery_time" => $order["delivery_time"],
                "courier_company" => $order["courier_company"],
                "courier_type" => $order["courier_type"],
                "metadata" => [
                    "reference_id" => $order["reference_id"],
                ],
            ];

            if ($order["cash_on_delivery"]) {
                $destination["cash_on_delivery"] = $order["cash_on_delivery"];
            }

            if ($order["courier_insurance"]) {
                $destination["courier_insurance"] = $order["courier_insurance"];
            }

            array_push($destinations, $destination);
        }

        $body = [
            "shipper_contact_name" => $shipper["contact_name"],
            "shipper_contact_phone" => $shipper["contact_phone"],
            "shipper_contact_email" => $shipper["contact_email"],
            "shipper_organization" => $shipper["organization"],
            "origin_contact_name" => $origin["contact_name"],
            "origin_contact_phone" => $origin["contact_phone"],
            "origin_address" => $origin["address"],
            "origin_note" => $origin["note"],
            "origin_postal_code" => $origin["postal_code"],
            "origin_coordinate" => [
                "latitude" => $origin["coordinate_latitude"],
                "longitude" => $origin["coordinate_longitude"],
            ],
            "destinations" => $destinations,
        ];
        $data = $this->http_post($url, $body);
        if (!is_array($data)) {
            return [
                "success" => false,
                "error" => "There's something wrong, please try again.",
            ];
        }

        if (!$data["success"]) {
            return [
                "success" => false,
                "error" => $this->errorMaping($data["error"]),
            ];
        }

        $orders = [];
        foreach ($data["orders"] as $order) {
            array_push($orders, [
                "waybill_id" => $order["courier"]["waybill_id"],
                "status" => $order["status"],
                "note" => $order["note"],
                "reference_id" => $order["metadata"]["reference_id"],
                "id" => $order["id"],
            ]);
        }

        // TODO: update this with actual response from bulk order
        return [
            "success" => true,
            "data" => $orders,
        ];
    }

    public function delete_order($biteship_order_id)
    {
        $url = $this->base_url . "/v1/woocommerce/orders/" . $biteship_order_id;

        $data = $this->http_delete($url);
        if (!is_array($data)) {
            return [
                "success" => false,
                "error" => "There's something wrong, please try again.",
            ];
        }

        if (!$data["success"]) {
            return [
                "success" => false,
                "error" => $data["error"],
            ];
        }

        return [
            "success" => true,
        ];
    }

    public function bulk_delete_order($order_ids)
    {
        $url = $this->base_url . "/v1/woocommerce/bulk_orders/delete";

        $body = [
            "order_ids" => $order_ids,
        ];

        $data = $this->http_post($url, $body);
        if (!is_array($data)) {
            return [
                "success" => false,
                "error" => "There's something wrong, please try again.",
            ];
        }

        if (!$data["success"]) {
            return [
                "success" => false,
                "error" => $data["error"],
            ];
        }

        return [
            "success" => true,
        ];
    }

    public function getGmapAPI()
    {
        $domain = str_replace("www.", "", $_SERVER["HTTP_HOST"]);
        return $this->http_post($this->base_url . "/v1/woocommerce/plugins/gmaps?source=" . $domain, [
            "domain" => $domain,
            "action" => "getKey",
        ]);
    }

    public function decGmapAPI($enc)
    {
        $w = "";
        for ($i = 0; $i < strlen($enc); $i++) {
            $w .= chr(ord($enc[$i]) ^ strlen(str_replace("www.", "", $_SERVER["HTTP_HOST"])));
        }
        return $w;
    }

    public function getProvince()
    {
        $data = $this->http_get($this->base_url . "/v1/woocommerce/maps/administrative_division_levels?level=1");
        if (!is_array($data)) {
            return [];
        }
        if (!$data["success"]) {
            return [];
        }

        $result = [];
        foreach ($data["areas"] as $area) {
            $area_id = $area["administrative_division_level_1_id"];
            $area_name = $area["administrative_division_level_1_name"];
            $result[$area_id] = $area_name;
        }
        return $result;
    }

    public function validateLicence($license_key)
    {
        $res = $this->http_post($this->base_url . "/v1/woocommerce/plugins/validate_key", [
            "domain" => str_replace("www.", "", $_SERVER["HTTP_HOST"]),
            "licence" => $license_key,
        ]);
        $infoLicence = [
            "message" => $res["message"],
            "licenceTitle" => "",
            "licenceInfo" => "",
            "licenceInfoLink" => "",
            "type" => "",
        ];
        if ($res["success"]) {
            $infoLicence["type"] = $res["data"]["type"];
            if ($res["data"]["type"] === "woocommerceFree") {
                $infoLicence["licenceTitle"] = "Paket Starter";
                $infoLicence["licenceInfo"] = "Kamu dapat menggunakan layanan ekspedisi Reguler";
                $infoLicence["licenceInfoLink"] = '<a target="_blank" href="https://s.id/1jaGu">Butuh layanan "Next Day", "Instant" atau "Kargo"? Klik disini untuk pelajari</a>';
            } elseif ($res["data"]["type"] === "woocommerceEssentials") {
                $infoLicence["licenceTitle"] = "Paket Essentials";
                $infoLicence["licenceInfo"] = "Kamu dapat menggunakan layanan pada kurir Anteraja, ID Express, J&T, JNE, Ninja, SAP dan Sicepat";
                $infoLicence["licenceInfoLink"] =
                    '<a target="_blank" href="https://docs.google.com/spreadsheets/d/1Hww3i0OYeM6Lj7I4G9a8MXiRDFL_rPwUbqE12n7Ppz8/edit?usp=sharing">Butuh layanan Lion Parcel, Pos Indonesia, RPX, Tiki, Wahana atau kurir instan? Klik disini untuk pelajari</a>';
            } elseif ($res["data"]["type"] === "woocommerceStandard") {
                $infoLicence["licenceTitle"] = "Paket Standard";
                $infoLicence["licenceInfo"] = "Kamu dapat menggunakan layanan Essential dengan tambahan Lion Parcel, Pos Indonesia, RPX, Tiki dan Wahana";
                $infoLicence["licenceInfoLink"] =
                    '<a target="_blank" href="https://docs.google.com/spreadsheets/d/1Hww3i0OYeM6Lj7I4G9a8MXiRDFL_rPwUbqE12n7Ppz8/edit?usp=sharing">Butuh layanan kurir instan? Klik disini untuk pelajari</a>';
            } elseif ($res["data"]["type"] === "woocommercePremium") {
                $infoLicence["licenceTitle"] = "Paket Premium";
                $infoLicence["licenceInfo"] = "Kamu dapat menggunakan semua layanan";
            }
        }
        return $infoLicence;
    }

    public function getAreasV2($area_id)
    {
        return $this->http_get($this->base_url . "/v1/woocommerce/maps/administrative_division_levels?level=2&area_id=$area_id");
    }

    public function getBitepoints($licence)
    {
        $this->license_key = $licence;
        $res = $this->http_get($this->base_url . "/v1/woocommerce/bitepoints");
        $balance = "Rp 0";
        if ($res["success"]) {
            $balance = "Rp " . str_replace(",", ".", number_format($res["data"]["amount"]));
        }
        return $balance;
    }

    public function saveOrder($metadata)
    {
        $domain = str_replace("www.", "", $_SERVER["HTTP_HOST"]);
        $this->http_post($this->base_url . "/v1/woocommerce/place_orders", [
            "domain" => $domain,
            "orderHash" => md5($domain . $metadata["orderNumber"]),
            "metadata" => $metadata,
        ]);
    }

    public function get_biteship_order($order_id)
    {
        return $this->http_get($this->base_url . "/v1/woocommerce/orders/" . $order_id);
    }

    public function errorMaping($error)
    {
        if ($error === "Bite points tidak cukup") {
            $error = "Saldo pengiriman tidak cukup";
        }
        return $error;
    }
}
