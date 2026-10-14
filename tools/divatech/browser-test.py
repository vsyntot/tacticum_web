# Run only against the isolated loopback rehearsal described in README.md.
from pathlib import Path
from urllib.parse import urlsplit
import json
from playwright.sync_api import sync_playwright
out=Path('/tmp/divatech-rehearsal');results=[]
with sync_playwright() as p:
 b=p.chromium.launch(channel='chrome',headless=True)
 for width in [1440,390]:
  context=b.new_context(viewport={'width':width,'height':1000},device_scale_factor=1)
  failed=[False];ids=[]
  def proxy(route):
   u=urlsplit(route.request.url)
   if u.hostname!='divatech.space':route.abort();return
   if u.path=='/local/rest/divatech_lead.php':
    ids.append(json.loads(route.request.post_data)['request_id'])
    if not failed[0]:
     failed[0]=True;route.fulfill(status=503,content_type='application/json',body='{"success":false}');return
   h=route.request.all_headers();h['host']='divatech.space'
   response=route.fetch(url='http://127.0.0.1:8766'+u.path+('?' + u.query if u.query else ''),headers=h)
   route.fulfill(response=response)
  context.route('**/*',proxy)
  page=context.new_page();errors=[];bad=[]
  page.on('pageerror',lambda e:errors.append(str(e)))
  page.goto('https://divatech.space/',wait_until='networkidle')
  assert page.locator('[data-divatech-form]').count()==2
  for y in range(0,page.locator('body').bounding_box()['height'].__int__(),700):page.evaluate('(y)=>window.scrollTo(0,y)',y);page.wait_for_timeout(100)
  page.wait_for_function('Array.from(document.images).every(i=>i.complete && i.naturalWidth>0)')
  page.evaluate('window.scrollTo(0,0)');page.screenshot(path=str(out/f'forms-{width}.png'),full_page=True)
  form=page.locator('#rec497697185 form');form.get_by_label('Your email').fill('synthetic@example.invalid');form.get_by_label('Your name').fill('Browser rehearsal');form.get_by_label('Your request').fill('Synthetic local browser test');form.locator('[name=consent]').check()
  form.get_by_role('button',name='Send',exact=True).click()
  page.wait_for_function('document.querySelector("#rec497697185 [data-form-status]").textContent.includes("could not confirm")')
  assert form.locator('[name=email]').input_value()=='synthetic@example.invalid'
  form.get_by_role('button',name='Send',exact=True).click()
  page.wait_for_function('document.querySelector("#rec497697185 [data-form-status]").textContent.includes("Thank you")')
  assert ids[0]==ids[1] and len(ids)==2
  assert form.locator('[name=email]').input_value()==''
  page.get_by_role('button',name='Request a call',exact=True).click();assert page.locator('dialog').is_visible()
  page.keyboard.press('Escape');assert not page.locator('dialog').is_visible()
  assert not errors,errors
  assert page.evaluate('document.documentElement.scrollWidth<=window.innerWidth')
  results.append({'width':width,'retry_same_id':True,'form_saved':True,'dialog_keyboard':True,'images_loaded':True,'page_errors':errors})
  context.close()
 b.close()
(out/'browser-forms-result.json').write_text(json.dumps(results,indent=2));print(json.dumps(results))
