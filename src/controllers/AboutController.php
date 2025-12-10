<?php

namespace Controllers;

use Httpeur\ControllerBase;

class AboutController extends ControllerBase
{
    public function index()
    {
        $this->render('about');
    }

    public function contact()
    {
        $this->render('about_contact');
    }

    public function contact_submit()
    {
        $values = $this->getFieldsValues(['name', 'email']);

        $data = [];
        if ($values['name'] == "narvalo") {
            $data["errors"] = ["name" => "Les narvalos ne sont pas autorisés à contacter l'équipe technique."];
        }
        if ($values['email'] == "narvalo@example.com") {
            $data["errors"] = ["email" => "Les emails narvalo ne sont pas autorisés à contacter l'équipe technique."];
        }
        if (empty($data["errors"])) {
            $data["success"] = true;
            $data["message"] = "Contact submitted successfully";
        } else {
            $data["name"] = $values['name'];
            $data["email"] = $values['email'];
        }

        $this->render('about_contact', $data);
    }
}
