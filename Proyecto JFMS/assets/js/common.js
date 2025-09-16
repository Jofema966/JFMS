
export async function postJSON(url, data){
  const r = await fetch(url, {method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify(data||{})});
  return r.json();
}
