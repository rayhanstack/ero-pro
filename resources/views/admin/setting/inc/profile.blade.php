 <div class="tab-pane fade show active" id="v-pills-profile" role="tabpanel">
     <div class="card">
         <div class="card-body p-4 p-md-5">
             <h5 class="card-title fw-bold mb-4">Public Profile</h5>

             <div class="d-flex align-items-center mb-4 pb-4 border-bottom">
                 <img src="https://ui-avatars.com/api/?name=Admin+User&background=4f46e5&color=fff"
                     class="rounded-circle me-4" width="80" height="80" alt="Avatar">
                 <div>
                     <div class="d-flex gap-2 mb-2">
                         <button class="btn btn-sm btn-primary">Upload new</button>
                         <button class="btn btn-sm btn-light text-danger">Remove</button>
                     </div>
                     <p class="small text-muted mb-0">Recommended size: 400x400px. Standard image files only.
                     </p>
                 </div>
             </div>

             <form class="needs-validation" novalidate>
                 <div class="row g-3">
                     <div class="col-md-6">
                         <label class="form-label fw-bold small">First Name</label>
                         <input type="text" class="form-control" value="Admin" required>
                     </div>
                     <div class="col-md-6">
                         <label class="form-label fw-bold small">Last Name</label>
                         <input type="text" class="form-control" value="User" required>
                     </div>
                     <div class="col-md-6">
                         <label class="form-label fw-bold small">Email Address</label>
                         <input type="email" class="form-control" value="admin@erppro.com" required>
                     </div>
                     <div class="col-md-6">
                         <label class="form-label fw-bold small">Phone Number</label>
                         <input type="tel" class="form-control" value="+1 (555) 123-4567">
                     </div>
                     <div class="col-12">
                         <label class="form-label fw-bold small">Bio</label>
                         <textarea class="form-control" rows="3" placeholder="Write a short summary about yourself..."></textarea>
                     </div>
                     <div class="col-12 mt-4">
                         <button type="submit" class="btn btn-primary px-4">Save Changes</button>
                     </div>
                 </div>
             </form>
         </div>
     </div>
 </div>
