<?php
namespace App\Controllers;

class FormTest extends BaseController
{
    public function index()
    {
        echo "<h1>Form Submission Test</h1>";
        
        // Check if form submitted
        if (!empty($_POST)) {
            echo "<h2 style='color: green;'>✅ RAW POST DATA RECEIVED!</h2>";
            echo "<pre>";
            var_dump($_POST);
            echo "</pre>";
            return;
        }
        
        echo '
        <div style="border: 3px solid red; padding: 20px;">
            <h3>Basic Form Test</h3>
            <form method="POST" action="/form-test">
                <input type="text" name="test_field" value="test_value">
                <button type="submit">SUBMIT RAW FORM</button>
            </form>
        </div>
        ';
    }
}