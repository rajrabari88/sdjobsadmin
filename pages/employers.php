<div class="p-4 p-md-5 bg-white rounded-4 shadow-sm border border-light-subtle">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark">Employer Directory</h3>
            <p class="text-muted mb-0">Manage registered companies, review their activity, and verify their status.</p>
        </div>
        <button class="btn btn-primary rounded-pill px-4 d-none d-sm-inline-block">
            <i class="bi bi-person-add me-2"></i> Add New Employer
        </button>
    </div>
    <hr>

    <div class="row g-3 mb-4 align-items-center">
        <div class="col-12 col-md-4">
            <input type="text" class="form-control rounded-pill" placeholder="Search by Company Name or Email...">
        </div>
        <div class="col-6 col-md-3">
            <select class="form-select rounded-pill">
                <option selected>Filter by Status</option>
                <option value="verified">Verified</option>
                <option value="pending">Pending Review</option>
                <option value="suspended">Suspended</option>
            </select>
        </div>
        <div class="col-6 col-md-3">
            <select class="form-select rounded-pill">
                <option selected>Sort by Jobs Posted</option>
                <option value="high">Highest First</option>
                <option value="low">Lowest First</option>
            </select>
        </div>
        <div class="col-12 col-md-2 text-md-end">
            <span class="text-muted small">Showing 1 to 10 of 28 entries</span>
        </div>
    </div>
    
    <div class="table-responsive">
        <table class="table table-striped table-hover align-middle">
            <thead class="table-dark" style="background-color: #1F2937;">
                <tr>
                    <th scope="col" style="width: 5%;">#</th>
                    <th scope="col" style="width: 25%;">Company Name</th>
                    <th scope="col" style="width: 25%;">Email & Contact</th>
                    <th scope="col" style="width: 15%;" class="text-center">Jobs Posted</th>
                    <th scope="col" style="width: 15%;" class="text-center">Status</th>
                    <th scope="col" style="width: 15%;" class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>1</td>
                    <td>
                        <div class="d-flex align-items-center">
                            <img src="https://placehold.co/35x35/D1E9FF/3B82F6?text=BT" class="rounded-circle me-3" alt="BridgeTech" width="35" height="35">
                            <span class="fw-semibold text-primary">BridgeTech Pvt. Ltd.</span>
                        </div>
                    </td>
                    <td>
                        hr@bridgetech.com<br>
                        <small class="text-muted">ID: 8745</small>
                    </td>
                    <td class="text-center fw-medium">12</td>
                    <td class="text-center">
                        <span class="badge rounded-pill bg-success px-3 py-2">Verified</span>
                    </td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-outline-primary me-1" title="View Profile"><i class="bi bi-eye"></i></button>
                        <button class="btn btn-sm btn-outline-danger" title="Suspend"><i class="bi bi-trash"></i></button>
                    </td>
                </tr>
                <tr>
                    <td>2</td>
                    <td>
                        <div class="d-flex align-items-center">
                            <img src="https://placehold.co/35x35/FFECD1/FFA000?text=GS" class="rounded-circle me-3" alt="GlobalSolutions" width="35" height="35">
                            <span class="fw-semibold">GlobalSolutions Inc.</span>
                        </div>
                    </td>
                    <td>
                        info@globalsol.com<br>
                        <small class="text-muted">ID: 9011</small>
                    </td>
                    <td class="text-center fw-medium">0</td>
                    <td class="text-center">
                        <span class="badge rounded-pill bg-warning text-dark px-3 py-2">Pending</span>
                    </td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-primary me-1" title="Verify"><i class="bi bi-check-lg"></i></button>
                        <button class="btn btn-sm btn-outline-secondary" title="View Application"><i class="bi bi-file-earmark-text"></i></button>
                    </td>
                </tr>
                <tr>
                    <td>3</td>
                    <td>
                        <div class="d-flex align-items-center">
                            <img src="https://placehold.co/35x35/FFE5E5/DC3545?text=AZ" class="rounded-circle me-3" alt="AlphaZone" width="35" height="35">
                            <span class="fw-semibold text-danger">AlphaZone Tech</span>
                        </div>
                    </td>
                    <td>
                        support@alphazone.net<br>
                        <small class="text-muted">ID: 7632</small>
                    </td>
                    <td class="text-center fw-medium">4</td>
                    <td class="text-center">
                        <span class="badge rounded-pill bg-danger px-3 py-2">Suspended</span>
                    </td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-outline-success me-1" title="Reactivate"><i class="bi bi-arrow-clockwise"></i></button>
                        <button class="btn btn-sm btn-outline-secondary" title="View Notes"><i class="bi bi-journal-text"></i></button>
                    </td>
                </tr>
                <tr>
                    <td>4</td>
                    <td>
                        <div class="d-flex align-items-center">
                            <img src="https://placehold.co/35x35/D1F6E9/059669?text=CT" class="rounded-circle me-3" alt="ClarityTech" width="35" height="35">
                            <span class="fw-semibold">ClarityTech Solutions</span>
                        </div>
                    </td>
                    <td>
                        jobs@claritytech.co<br>
                        <small class="text-muted">ID: 9981</small>
                    </td>
                    <td class="text-center fw-bold">28</td>
                    <td class="text-center">
                        <span class="badge rounded-pill bg-success px-3 py-2">Verified</span>
                    </td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-outline-primary me-1" title="View Profile"><i class="bi bi-eye"></i></button>
                        <button class="btn btn-sm btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <nav class="mt-4">
      <ul class="pagination justify-content-end">
        <li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>
        <li class="page-item active"><a class="page-link" href="#">1</a></li>
        <li class="page-item"><a class="page-link" href="#">2</a></li>
        <li class="page-item"><a class="page-link" href="#">3</a></li>
        <li class="page-item"><a class="page-link" href="#">Next</a></li>
      </ul>
    </nav>
</div>