<?php

namespace NIIT\ESign\Http\Controllers;

use Illuminate\Http\Request;
use NIIT\ESign\Http\Requests\TemplateRequest;
use NIIT\ESign\Models\Template;

class TemplateController extends Controller
{
    public function index(Request $request)
    {
        $templates = Template::all();

        return $this->view('esign::templates.index', compact('templates'));
    }

    public function store(TemplateRequest $request)
    {
        Template::create($request->all());

        return redirect()->route('esign.templates.index');
    }

    public function show(Template $template)
    {
        return $this->view('esign::templates.show', compact('template'));
    }

    public function update(TemplateRequest $request, Template $template)
    {
        $template->update($request->all());

        return redirect()->route('esign.templates.index');
    }

    public function destroy(Template $template)
    {
        $template->delete();

        return back();
    }
}
