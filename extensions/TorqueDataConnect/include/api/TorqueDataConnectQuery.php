<?php
class TorqueDataConnectQuery extends APIBase {

  public function __construct($main, $action) {
    parent::__construct($main, $action);
  }

  public function execute() {
    $valid_group = "";
    foreach($this->getUser()->getGroups() as $group) {
      error_log($group);
      if(in_array($group, ["bureaucrat", "torqueapi"])) {
        $valid_group = $group;
        break;
      }
    }

    $contents = file_get_contents(
      "http://localhost:5000/api" .
      $this->getParameter("path") .
      "?group=" .
      $valid_group
      );

    $response = json_decode($contents);
    foreach($response as $name => $value) {
      $this->getResult()->addValue(null, $name, $value);
    }
  }

  public function getAllowedParams() {
    return [
      "path" => [
        ApiBase::PARAM_TYPE => 'string',
        ApiBase::PARAM_REQUIRED => 'true'
      ],
    ];
  }
}
?>