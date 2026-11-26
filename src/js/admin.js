const $ = id => document.getElementById(id); let editing = null;
const api = (path, options={}) => fetch('/api' + path, {headers:{'Content-Type':'application/json'}, ...options}).then(async r => { const x=await r.json(); if(!r.ok) throw Error(x.error||'Request failed'); return x; });
function msg(x){$('message').textContent=x}
async function login(){try{await api('/login',{method:'POST',body:JSON.stringify({username:$('username').value,password:$('password').value})}); showEditor();}catch(e){msg(e.message)}}
async function setup(){try{await api('/setup',{method:'POST',body:JSON.stringify({username:$('username').value,password:$('password').value})});msg('Admin created. Login now.')}catch(e){msg(e.message)}}
async function showEditor(){ $('login').hidden=true; $('editor').hidden=false; await listPosts(); }
async function listPosts(){const p=await api('/posts'); $('posts').innerHTML=p.map(x=>`<p><a href="#" onclick="editPost('${x.slug}');return false">${x.title}</a> <button onclick="deletePost('${x.slug}')">Delete</button></p>`).join('')}
async function editPost(slug){const p=(await api('/posts')).find(x=>x.slug===slug); editing=p; $('slug').value=p.slug;$('slug').disabled=true;$('title').value=p.title;$('date').value=p.date;$('excerpt').value=p.excerpt||'';$('markdown').value=await fetch('/content/blog/'+p.file).then(r=>r.text())}
async function savePost(){const data={slug:$('slug').value,title:$('title').value,date:$('date').value,excerpt:$('excerpt').value,markdown:$('markdown').value};try{await api(editing?'/posts/'+editing.slug:'/posts',{method:editing?'PUT':'POST',body:JSON.stringify(data)});editing=null;$('slug').disabled=false;msg('Saved');['slug','title','date','excerpt','markdown'].forEach(x=>$(x).value='');await listPosts()}catch(e){msg(e.message)}}
async function deletePost(slug){if(confirm('Delete this post?')){await api('/posts/'+slug,{method:'DELETE'});await listPosts()}}
async function logout(){await api('/logout',{method:'POST'});location.reload()}
api('/me').then(x=>x.authenticated&&showEditor());
