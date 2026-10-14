# Run only against the isolated loopback rehearsal described in README.md.
import json,re,uuid,urllib.request,urllib.error
from http.cookies import SimpleCookie
base='http://127.0.0.1:8766';checks=[]
def call(path='/',data=None,method=None,headers=None):
 h={'Host':'divatech.space','Origin':'https://divatech.space'};h.update(headers or {})
 if 'Cookie' not in h and globals().get('cookie'):h['Cookie']=cookie
 raw=None if data is None else (data if isinstance(data,bytes) else json.dumps(data).encode())
 if raw is not None:h['Content-Type']='application/json'
 req=urllib.request.Request(base+path,data=raw,headers=h,method=method)
 try:r=urllib.request.urlopen(req)
 except urllib.error.HTTPError as e:r=e
 return r.status,r.read().decode(),r.headers
status,html,heads=call();assert status==200
c=SimpleCookie();c.load(heads.get('Set-Cookie'));cookie='; '.join(k+'='+v.value for k,v in c.items())
token=re.search(r'name="sessid" value="([^"]+)"',html)[1]
assert len(re.findall('data-divatech-form',html))==2
valid={'sessid':token,'request_id':str(uuid.uuid4()),'kind':'contact','email':'synthetic@example.invalid','name':'Synthetic test','message':'Local test only','consent':True}
endpoint='/local/rest/divatech_lead.php'
def expect(label,status,data=valid,headers=None,method=None,path=endpoint):
 s,b,_=call(path,data,method,headers);assert s==status,(label,s,b[:500]);checks.append({'test':label,'status':s});return b
expect('strict csrf',403,valid|{'sessid':'invalid'})
expect('cross origin',403,headers={'Origin':'https://tacticum.ru'})
expect('bad origin cannot use referer',403,headers={'Origin':'null','Referer':'https://divatech.space/'})
expect('s1 host',403,headers={'Host':'tacticum.ru'})
expect('GET rejected',405,data=None,method='GET')
expect('malformed JSON',400,b'{')
expect('oversized body',413,b' '+b'x'*17000)
expect('invalid fields',422,valid|{'email':'bad'})
expect('contact saved',200)
expect('duplicate returns success',200)
expect('callback saved',200,valid|{'request_id':str(uuid.uuid4()),'kind':'callback','phone':'+90 500 000 00 00','email':'','name':'','message':''})
expect('Tacticum endpoint on s2 blocked',403,path='/local/rest/tacticum_form.php')
limited=False
for i in range(25):
 s,b,_=call(endpoint,valid)
 if s==429:limited=True;break
assert limited
checks.append({'test':'rate limit','status':429})
print(json.dumps(checks,indent=2))
open('/tmp/divatech-rehearsal/http-tests.json','w').write(json.dumps(checks,indent=2))
