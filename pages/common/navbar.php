<nav class="navbar navbar-expand-lg fixed-top">
  <div class="container-fluid px-0">
    <a class="navbar-brand" href="<?= ROOT_PATH ?>pages/index.php">
      <!-- Logo modo claro -->
      <img src="<?= BASE_URL ?>assets/images/logos/logoMainNegro.png" alt="Subsecretaría de Empleo"
        class="d-inline d-dark-none img-fluid" style="max-height: 40px;" />

      <!-- Logo modo oscuro -->
      <img src="<?= BASE_URL ?>assets/images/logos/logoMainBlanco.png" alt="Subsecretaría de Empleo"
        class="d-none d-dark-inline img-fluid" style="max-height: 40px;" />
    </a>


    <div class="ms-auto d-flex align-items-center order-lg-3">
      <div class="dropdown">
        <button class="btn btn-light btn-icon rounded-circle d-flex align-items-center" type="button"
          aria-expanded="false" data-bs-toggle="dropdown" aria-label="Toggle theme (auto)">
          <i class="bi theme-icon-active"></i>
          <span class="visually-hidden bs-theme-text">Toggle theme</span>
        </button>
        <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="bs-theme-text">
          <li>
            <button type="button" class="dropdown-item d-flex align-items-center" data-bs-theme-value="light"
              aria-pressed="false">
              <i class="bi theme-icon bi-sun-fill"></i>
              <span class="ms-2">Light</span>
            </button>
          </li>
          <li>
            <button type="button" class="dropdown-item d-flex align-items-center" data-bs-theme-value="dark"
              aria-pressed="false">
              <i class="bi theme-icon bi-moon-stars-fill"></i>
              <span class="ms-2">Dark</span>
            </button>
          </li>
          <li>
            <button type="button" class="dropdown-item d-flex align-items-center active" data-bs-theme-value="auto"
              aria-pressed="true">
              <i class="bi theme-icon bi-circle-half"></i>
              <span class="ms-2">Auto</span>
            </button>
          </li>
        </ul>
      </div>
      <ul class="navbar-nav navbar-right-wrap ms-2 flex-row ">
        <li class="dropdown ms-2 d-inline-block position-static">
          <a class="rounded-circle" href="#" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false">
            <div class="avatar avatar-md avatar-indicators avatar-online">
              <img alt="avatar" src="<?= BASE_URL ?>assets/images/default_profiles/perfil-individual.png" class="rounded-circle" />
            </div>
          </a>
          <div class="dropdown-menu dropdown-menu-end position-absolute mx-3 my-5">
            <div class="dropdown-item">
              <div class="d-flex">
                <div class="avatar avatar-md avatar-indicators avatar-online">
                  <img alt="avatar" src="<?= BASE_URL ?>assets/images/default_profiles/perfil-individual.png" class="rounded-circle" />
                </div>
                <div class="ms-3 lh-1">
                  <h5 class="mb-1"><?php echo $usuarioActual->nombre; ?></h5>
                  <p class="mb-0"><?php echo $usuarioActual->apellido; ?></p>
                </div>
              </div>
            </div>
 
            <div class="dropdown-divider"></div>
            <ul class="list-unstyled">
              <li>
                  <a class="dropdown-item" href="<?= ROOT_PATH ?>manuales/index.php" target="_blank" rel="noopener noreferrer">
                      <i class="fe fe-star me-2"></i>
                      Manuales
                  </a>
              </li>
            </ul>
            <div class="dropdown-divider"></div>
            <ul class="list-unstyled">
             <!--  <li>
                <a class="dropdown-item" >
                  <i class="fe fe-bell me-2"></i>
                  Notificame &nbsp;<input type="checkbox" class="form-check cursor-pointer pointer" name="notificame" value="1" id="notificame" title="Mantenme al tanto de todo :v">
                </a>
              </li> -->
              <li>
                <a class="dropdown-item" href="<?= ROOT_PATH ?>backend/controller/auth/logout.php">
                  <i class="fe fe-power me-2"></i>
                  Salir
                </a>
              </li>
            </ul>
          </div>
        </li>
      </ul>
    </div>
    <div>
      <!-- Button -->
      <button class="navbar-toggler collapsed ms-2 d-none" type="button" data-bs-toggle="collapse"
        data-bs-target="#navbar-default" aria-controls="navbar-default" aria-expanded="false"
        aria-label="Toggle navigation">
        <span class="icon-bar top-bar mt-0"></span>
        <span class="icon-bar middle-bar"></span>
        <span class="icon-bar bottom-bar"></span>
      </button>
    </div>
    <!-- Collapse -->
    <div class="collapse navbar-collapse" id="navbar-default">
      <ul class="navbar-nav mt-3 mt-lg-0 mx-xxl-auto d-none">
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="navbarBrowse" data-bs-toggle="dropdown" aria-haspopup="true"
            aria-expanded="false" data-bs-display="static">Categories</a>
          <ul class="dropdown-menu dropdown-menu-arrow" aria-labelledby="navbarBrowse">
            <li class="dropdown-submenu dropend">
              <a class="dropdown-item dropdown-list-group-item dropdown-toggle" href="#">Web Development</a>
              <ul class="dropdown-menu">
                <li>
                  <a class="dropdown-item" href="<?= BASE_URL ?>pages/course-category.html">Bootstrap</a>
                </li>
                <li>
                  <a class="dropdown-item" href="<?= BASE_URL ?>pages/course-category.html">React</a>
                </li>
                <li>
                  <a class="dropdown-item" href="<?= BASE_URL ?>pages/course-category.html">GraphQl</a>
                </li>
                <li>
                  <a class="dropdown-item" href="<?= BASE_URL ?>pages/course-category.html">Gatsby</a>
                </li>
                <li>
                  <a class="dropdown-item" href="<?= BASE_URL ?>pages/course-category.html">Grunt</a>
                </li>
                <li>
                  <a class="dropdown-item" href="<?= BASE_URL ?>pages/course-category.html">Svelte</a>
                </li>
                <li>
                  <a class="dropdown-item" href="<?= BASE_URL ?>pages/course-category.html">Meteor</a>
                </li>
                <li>
                  <a class="dropdown-item" href="<?= BASE_URL ?>pages/course-category.html">HTML5</a>
                </li>
                <li>
                  <a class="dropdown-item" href="<?= BASE_URL ?>pages/course-category.html">Angular</a>
                </li>
              </ul>
            </li>
            <li class="dropdown-submenu dropend">
              <a class="dropdown-item dropdown-list-group-item dropdown-toggle" href="#">Design</a>
              <ul class="dropdown-menu">
                <li>
                  <a class="dropdown-item" href="<?= BASE_URL ?>pages/course-category.html">Graphic Design</a>
                </li>
                <li>
                  <a class="dropdown-item" href="<?= BASE_URL ?>pages/course-category.html">Illustrator</a>
                </li>
                <li>
                  <a class="dropdown-item" href="<?= BASE_URL ?>pages/course-category.html">UX / UI Design</a>
                </li>
                <li>
                  <a class="dropdown-item" href="<?= BASE_URL ?>pages/course-category.html">Figma Design</a>
                </li>
                <li>
                  <a class="dropdown-item" href="<?= BASE_URL ?>pages/course-category.html">Adobe XD</a>
                </li>
                <li>
                  <a class="dropdown-item" href="<?= BASE_URL ?>pages/course-category.html">Sketch</a>
                </li>
                <li>
                  <a class="dropdown-item" href="<?= BASE_URL ?>pages/course-category.html">Icon Design</a>
                </li>
                <li>
                  <a class="dropdown-item" href="<?= BASE_URL ?>pages/course-category.html">Photoshop</a>
                </li>
              </ul>
            </li>
            <li>
              <a href="<?= BASE_URL ?>pages/course-category.html" class="dropdown-item">Mobile App</a>
            </li>
            <li>
              <a href="<?= BASE_URL ?>pages/course-category.html" class="dropdown-item">IT Software</a>
            </li>
            <li>
              <a href="<?= BASE_URL ?>pages/course-category.html" class="dropdown-item">Marketing</a>
            </li>
            <li>
              <a href="<?= BASE_URL ?>pages/course-category.html" class="dropdown-item">Music</a>
            </li>
            <li>
              <a href="<?= BASE_URL ?>pages/course-category.html" class="dropdown-item">Life Style</a>
            </li>
            <li>
              <a href="<?= BASE_URL ?>pages/course-category.html" class="dropdown-item">Business</a>
            </li>
            <li>
              <a href="<?= BASE_URL ?>pages/course-category.html" class="dropdown-item">Photography</a>
            </li>
          </ul>
        </li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="navbarLanding" data-bs-toggle="dropdown" aria-haspopup="true"
            aria-expanded="false">Landings</a>
          <ul class="dropdown-menu" aria-labelledby="navbarLanding">
            <li>
              <h4 class="dropdown-header">Landings</h4>
            </li>
            <li>
              <a href="<?= BASE_URL ?>index.html" class="dropdown-item">
                <span>Home Default</span>
              </a>
            </li>
            <li>
              <a href="<?= BASE_URL ?>pages/landings/landing-abroad.html" class="dropdown-item">
                <span>Home Abroad</span>
              </a>
            </li>
            <li>
              <a href="<?= BASE_URL ?>mentor/mentor.html" class="dropdown-item">
                <span>Home Mentor</span>
              </a>
            </li>
            <li>
              <a href="<?= BASE_URL ?>pages/landings/landing-education.html" class="dropdown-item">Home Education</a>
            </li>
            <li>
              <a href="<?= BASE_URL ?>pages/landings/home-academy.html" class="dropdown-item">Home Academy</a>
            </li>
            <li>
              <a href="<?= BASE_URL ?>pages/landings/landing-courses.html" class="dropdown-item">Home Courses</a>
            </li>
            <li>
              <a href="<?= BASE_URL ?>pages/landings/landing-sass.html" class="dropdown-item">Home Sass</a>
            </li>
            <li class="border-bottom my-2"></li>
            <li>
              <a href="<?= BASE_URL ?>pages/landings/course-lead.html" class="dropdown-item">Lead Course</a>
            </li>
            <li>
              <a href="<?= BASE_URL ?>pages/landings/request-access.html" class="dropdown-item">Request Access</a>
            </li>

            <li>
              <a href="<?= BASE_URL ?>pages/landings/landing-job.html" class="dropdown-item">Job Listing</a>
            </li>
          </ul>
        </li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="navbarPages" data-bs-toggle="dropdown" aria-haspopup="true"
            aria-expanded="false">Pages</a>
          <ul class="dropdown-menu dropdown-menu-arrow" aria-labelledby="navbarPages">
            <li class="dropdown-submenu dropend">
              <a class="dropdown-item dropdown-list-group-item dropdown-toggle" href="#">Courses</a>
              <ul class="dropdown-menu">
                <li>
                  <a class="dropdown-item" href="<?= BASE_URL ?>pages/course-filter-grid.html">Course Grid</a>
                </li>
                <li>
                  <a class="dropdown-item" href="<?= BASE_URL ?>pages/course-filter-list.html">Course List</a>
                </li>
                <li class="border-bottom my-2"></li>

                <li>
                  <a class="dropdown-item" href="<?= BASE_URL ?>pages/course-category.html">Course Category v1</a>
                </li>
                <li>
                  <a class="dropdown-item" href="<?= BASE_URL ?>pages/course-category-v2.html">Course Category v2</a>
                </li>
                <li class="border-bottom my-2"></li>

                <li>
                  <a class="dropdown-item" href="<?= BASE_URL ?>pages/course-single.html">Course Single v1</a>
                </li>
                <li>
                  <a class="dropdown-item" href="<?= BASE_URL ?>pages/course-single-v2.html">Course Single v2</a>
                </li>
                <li>
                  <a class="dropdown-item" href="<?= BASE_URL ?>pages/course-single-v3.html">Course Single v3</a>
                </li>
                <li class="border-bottom my-2"></li>
                <li>
                  <a class="dropdown-item" href="<?= BASE_URL ?>pages/course-resume.html">Course Resume</a>
                </li>
                <li>
                  <a class="dropdown-item" href="<?= BASE_URL ?>pages/course-checkout.html">Course Checkout</a>
                </li>
                <li>
                  <a class="dropdown-item" href="<?= BASE_URL ?>pages/add-course.html">Add New Course</a>
                </li>
              </ul>
            </li>
            <li>
              <a class="dropdown-item" href="<?= BASE_URL ?>pages/dashboard-project.html">
                Projects
                <span class="badge bg-primary ms-2">New</span>
              </a>
            </li>
            <li>
              <a class="dropdown-item" href="<?= BASE_URL ?>pages/dashboard-quiz.html">
                Quizzes
                <span class="badge bg-primary ms-2">New</span>
              </a>
            </li>
            <li class="dropdown-submenu dropend">
              <a class="dropdown-item dropdown-list-group-item dropdown-toggle" href="#">Paths</a>
              <ul class="dropdown-menu">
                <li>
                  <a href="<?= BASE_URL ?>pages/course-path.html" class="dropdown-item">Browse Path</a>
                </li>
                <li>
                  <a href="<?= BASE_URL ?>pages/course-path-single.html" class="dropdown-item">Path Single</a>
                </li>
              </ul>
            </li>
            <li class="dropdown-submenu dropend">
              <a class="dropdown-item dropdown-list-group-item dropdown-toggle" href="#">Blog</a>
              <ul class="dropdown-menu">
                <li>
                  <a class="dropdown-item" href="<?= BASE_URL ?>pages/blog.html">Listing</a>
                </li>
                <li>
                  <a class="dropdown-item" href="<?= BASE_URL ?>pages/blog-single.html">Article</a>
                </li>
                <li>
                  <a class="dropdown-item" href="<?= BASE_URL ?>pages/blog-category.html">Category</a>
                </li>
                <li>
                  <a class="dropdown-item" href="<?= BASE_URL ?>pages/blog-sidebar.html">Sidebar</a>
                </li>
              </ul>
            </li>

            <li class="dropdown-submenu dropend">
              <a class="dropdown-item dropdown-list-group-item dropdown-toggle" href="#">Career</a>
              <ul class="dropdown-menu">
                <li>
                  <a class="dropdown-item" href="<?= BASE_URL ?>pages/career.html">Overview</a>
                </li>
                <li>
                  <a class="dropdown-item" href="<?= BASE_URL ?>pages/career-list.html">Listing</a>
                </li>
                <li>
                  <a class="dropdown-item" href="<?= BASE_URL ?>pages/career-single.html">Opening</a>
                </li>
              </ul>
            </li>
            <li class="dropdown-submenu dropend">
              <a class="dropdown-item dropdown-list-group-item dropdown-toggle" href="#">Portfolio</a>
              <ul class="dropdown-menu">
                <li>
                  <a class="dropdown-item" href="<?= BASE_URL ?>pages/portfolio.html">List</a>
                </li>
                <li>
                  <a class="dropdown-item" href="<?= BASE_URL ?>pages/portfolio-single.html">Single</a>
                </li>
              </ul>
            </li>
            <li class="dropdown-submenu dropend">
              <a class="dropdown-item dropdown-list-group-item dropdown-toggle" href="#">
                <span>Mentor</span>
              </a>
              <ul class="dropdown-menu">
                <li>
                  <a class="dropdown-item" href="<?= BASE_URL ?>mentor/mentor.html">Home</a>
                </li>
                <li>
                  <a class="dropdown-item" href="<?= BASE_URL ?>mentor/mentor-list.html">List</a>
                </li>
                <li>
                  <a class="dropdown-item" href="<?= BASE_URL ?>mentor/mentor-single.html">Single</a>
                </li>
              </ul>
            </li>
            <li class="dropdown-submenu dropend">
              <a class="dropdown-item dropdown-list-group-item dropdown-toggle" href="#">Job</a>
              <ul class="dropdown-menu">
                <li>
                  <a class="dropdown-item" href="<?= BASE_URL ?>pages/landings/landing-job.html">Home</a>
                </li>
                <li>
                  <a class="dropdown-item" href="<?= BASE_URL ?>pages/jobs/job-listing.html">List</a>
                </li>
                <li>
                  <a class="dropdown-item" href="<?= BASE_URL ?>pages/jobs/job-grid.html">Grid</a>
                </li>
                <li>
                  <a class="dropdown-item" href="<?= BASE_URL ?>pages/jobs/job-single.html">Single</a>
                </li>
                <li>
                  <a class="dropdown-item" href="<?= BASE_URL ?>pages/jobs/company-list.html">Company List</a>
                </li>
                <li>
                  <a class="dropdown-item" href="<?= BASE_URL ?>pages/jobs/company-about.html">Company Single</a>
                </li>
              </ul>
            </li>
            <li class="dropdown-submenu dropend">
              <a class="dropdown-item dropdown-list-group-item dropdown-toggle" href="#">Specialty</a>
              <ul class="dropdown-menu">
                <li>
                  <a class="dropdown-item" href="<?= BASE_URL ?>pages/coming-soon.html">Coming Soon</a>
                </li>
                <li>
                  <a class="dropdown-item" href="<?= BASE_URL ?>pages/404-error.html">Error 404</a>
                </li>
                <li>
                  <a class="dropdown-item" href="<?= BASE_URL ?>pages/maintenance-mode.html">Maintenance Mode</a>
                </li>
                <li>
                  <a class="dropdown-item" href="<?= BASE_URL ?>pages/terms-condition-page.html">Terms & Conditions</a>
                </li>
              </ul>
            </li>
            <li>
              <hr class="mx-3" />
            </li>

            <li>
              <a class="dropdown-item" href="<?= BASE_URL ?>pages/about.html">About</a>
            </li>

            <li class="dropdown-submenu dropend">
              <a class="dropdown-item dropdown-list-group-item dropdown-toggle" href="#">Help Center</a>
              <ul class="dropdown-menu">
                <li>
                  <a class="dropdown-item" href="<?= BASE_URL ?>pages/help-center.html">Help Center</a>
                </li>
                <li>
                  <a class="dropdown-item" href="<?= BASE_URL ?>pages/help-center-faq.html">FAQ's</a>
                </li>
                <li>
                  <a class="dropdown-item" href="<?= BASE_URL ?>pages/help-center-guide.html">Guide</a>
                </li>
                <li>
                  <a class="dropdown-item" href="<?= BASE_URL ?>pages/help-center-guide-single.html">Guide Single</a>
                </li>
                <li>
                  <a class="dropdown-item" href="<?= BASE_URL ?>pages/help-center-support.html">Support</a>
                </li>
              </ul>
            </li>
            <li>
              <a class="dropdown-item" href="<?= BASE_URL ?>pages/pricing.html">Pricing</a>
            </li>
            <li>
              <a class="dropdown-item" href="<?= BASE_URL ?>pages/compare-plan.html">Compare Plan</a>
            </li>

            <li>
              <a class="dropdown-item" href="<?= BASE_URL ?>pages/contact.html">Contact</a>
            </li>
            <li class="dropdown-submenu dropend">
              <a class="dropdown-item dropdown-toggle" href="#">Dropdown levels</a>
              <ul class="dropdown-menu dropdown-menu-start" data-bs-popper="none">
                <li><a class="dropdown-item" href="#">Dropdown item</a></li>
                <li><a class="dropdown-item" href="#">Dropdown item</a></li>
                <li><a class="dropdown-item" href="#">Dropdown item</a></li>
                <!-- dropdown submenu open right -->
                <li class="dropdown-submenu dropend">
                  <a class="dropdown-item dropdown-toggle" href="#">Dropdown (end)</a>
                  <ul class="dropdown-menu" data-bs-popper="none">
                    <li><a class="dropdown-item" href="#">Dropdown item</a></li>
                    <li><a class="dropdown-item" href="#">Dropdown item</a></li>
                  </ul>
                </li>

                <!-- dropdown submenu open left -->
                <li class="dropdown-submenu dropstart">
                  <a class="dropdown-item dropdown-toggle" href="#">Dropdown (start)</a>
                  <ul class="dropdown-menu" data-bs-popper="none">
                    <li><a class="dropdown-item" href="#">Dropdown item</a></li>
                    <li><a class="dropdown-item" href="#">Dropdown item</a></li>
                  </ul>
                </li>
              </ul>
            </li>
          </ul>
        </li>

        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="navbarAccount" data-bs-toggle="dropdown" aria-haspopup="true"
            aria-expanded="false">Accounts</a>
          <ul class="dropdown-menu dropdown-menu-arrow" aria-labelledby="navbarAccount">
            <li>
              <h4 class="dropdown-header">Accounts</h4>
            </li>
            <li class="dropdown-submenu dropend">
              <a class="dropdown-item dropdown-list-group-item dropdown-toggle" href="#">
                Instructor
                <span class="badge bg-primary ms-2">New</span>
              </a>
              <ul class="dropdown-menu">
                <li class="text-wrap">
                  <h5 class="dropdown-header text-dark">Instructor</h5>
                  <p class="dropdown-text mb-0">Instructor dashboard for manage courses and earning.</p>
                </li>
                <li>
                  <hr class="mx-3" />
                </li>
                <li>
                  <a class="dropdown-item" href="<?= BASE_URL ?>pages/dashboard-instructor.html">Dashboard</a>
                </li>
                <li>
                  <a class="dropdown-item" href="<?= BASE_URL ?>pages/instructor-profile.html">Profile</a>
                </li>
                <li>
                  <a class="dropdown-item" href="<?= BASE_URL ?>pages/instructor-courses.html">My Courses</a>
                </li>
                <li>
                  <a class="dropdown-item" href="<?= BASE_URL ?>pages/instructor-order.html">Orders</a>
                </li>
                <li>
                  <a class="dropdown-item" href="<?= BASE_URL ?>pages/instructor-reviews.html">Reviews</a>
                </li>
                <li>
                  <a class="dropdown-item" href="<?= BASE_URL ?>pages/instructor-students.html">Students</a>
                </li>
                <li>
                  <a class="dropdown-item" href="<?= BASE_URL ?>pages/instructor-payouts.html">Payouts</a>
                </li>
                <li>
                  <a class="dropdown-item" href="<?= BASE_URL ?>pages/instructor-earning.html">Earning</a>
                </li>
                <li class="dropdown-submenu dropend">
                  <a class="dropdown-item dropdown-list-group-item dropdown-toggle" href="#">Quiz</a>
                  <ul class="dropdown-menu">
                    <li>
                      <a class="dropdown-item" href="<?= BASE_URL ?>pages/instructor-quiz.html">Quiz</a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="<?= BASE_URL ?>pages/instructor-quiz-details.html">Single</a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="<?= BASE_URL ?>pages/instructor-quiz-result.html">Result</a>
                    </li>
                  </ul>
                </li>
              </ul>
            </li>
            <li class="dropdown-submenu dropend">
              <a class="dropdown-item dropdown-list-group-item dropdown-toggle" href="#">
                Students
                <span class="badge bg-primary ms-2">New</span>
              </a>
              <ul class="dropdown-menu">
                <li class="text-wrap">
                  <h5 class="dropdown-header text-dark">Students</h5>
                  <p class="dropdown-text mb-0">Students dashboard to manage your courses and subscriptions.</p>
                </li>
                <li>
                  <hr class="mx-3" />
                </li>
                <li>
                  <a class="dropdown-item" href="<?= BASE_URL ?>pages/dashboard-student.html">Dashboard</a>
                </li>
                <li>
                  <a class="dropdown-item" href="<?= BASE_URL ?>pages/student-subscriptions.html">Subscriptions</a>
                </li>
                <li>
                  <a class="dropdown-item" href="<?= BASE_URL ?>pages/payment-method.html">Payments</a>
                </li>
                <li>
                  <a class="dropdown-item" href="<?= BASE_URL ?>pages/billing-info.html">Billing Info</a>
                </li>
                <li class="dropdown-submenu dropend">
                  <a class="dropdown-item dropdown-list-group-item dropdown-toggle" href="#">Invoice</a>
                  <ul class="dropdown-menu">
                    <li>
                      <a class="dropdown-item" href="<?= BASE_URL ?>pages/invoice.html">Invoice</a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="<?= BASE_URL ?>pages/invoice-details.html">Invoice Details</a>
                    </li>
                  </ul>
                </li>

                <li>
                  <a class="dropdown-item" href="<?= BASE_URL ?>pages/dashboard-student.html">Bookmarked</a>
                </li>
                <li>
                  <a class="dropdown-item" href="<?= BASE_URL ?>pages/dashboard-student.html">My Path</a>
                </li>
                <li>
                  <a class="dropdown-item" href="<?= BASE_URL ?>pages/all-courses.html">All Courses</a>
                </li>
                <li>
                  <a class="dropdown-item" href="<?= BASE_URL ?>pages/learning-path.html">Learning Path</a>
                </li>

                <li class="dropdown-submenu dropend">
                  <a class="dropdown-item dropdown-list-group-item dropdown-toggle" href="#">Quiz</a>
                  <ul class="dropdown-menu">
                    <li>
                      <a class="dropdown-item" href="<?= BASE_URL ?>pages/student-quiz.html">Quiz</a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="<?= BASE_URL ?>pages/quiz-blank.html">Quiz Blank</a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="<?= BASE_URL ?>pages/my-quiz.html">My Quiz</a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="<?= BASE_URL ?>pages/student-quiz-attempt.html">Quiz Attempt</a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="<?= BASE_URL ?>pages/student-quiz-start.html">Quiz Single</a>
                    </li>

                    <li>
                      <a class="dropdown-item" href="<?= BASE_URL ?>pages/quiz-result.html">Quiz Result</a>
                    </li>
                  </ul>
                </li>
                <li class="dropdown-submenu dropend">
                  <a class="dropdown-item dropdown-list-group-item dropdown-toggle" href="#">Certificate</a>
                  <ul class="dropdown-menu">
                    <li>
                      <a class="dropdown-item" href="<?= BASE_URL ?>pages/certificate-blank.html">Certificate</a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="<?= BASE_URL ?>pages/my-certificate.html">My Certificate</a>
                    </li>
                  </ul>
                </li>
                <li class="dropdown-submenu dropend">
                  <a class="dropdown-item dropdown-list-group-item dropdown-toggle" href="#">Learning</a>
                  <ul class="dropdown-menu">
                    <li>
                      <a class="dropdown-item" href="<?= BASE_URL ?>pages/my-learning.html">My Learning</a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="<?= BASE_URL ?>pages/learning-single.html">Learning Single</a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="<?= BASE_URL ?>pages/learning-path-single.html">Learning Path
                        Single</a>
                    </li>
                  </ul>
                </li>
                <li class="dropdown-submenu dropend">
                  <a class="dropdown-item dropdown-list-group-item dropdown-toggle" href="#">My Projects</a>
                  <ul class="dropdown-menu">
                    <li>
                      <a class="dropdown-item" href="<?= BASE_URL ?>pages/project-blank.html">Project Blank</a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="<?= BASE_URL ?>pages/dashboard-project.html">Dashboard Project</a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="<?= BASE_URL ?>pages/project-single.html">Project Single</a>
                    </li>
                  </ul>
                </li>
              </ul>
            </li>
            <li class="dropdown-submenu dropend">
              <a class="dropdown-item dropdown-list-group-item dropdown-toggle" href="#">Admin</a>
              <ul class="dropdown-menu">
                <li class="text-wrap">
                  <h5 class="dropdown-header text-dark">Master Admin</h5>
                  <p class="dropdown-text mb-0">Master admin dashboard to manage courses, user, site setting , and
                    work with amazing apps.</p>
                </li>
                <li>
                  <hr class="mx-3" />
                </li>
                <li class="px-3 d-grid">
                  <a href="<?= BASE_URL ?>pages/dashboard/admin-dashboard.html" class="btn btn-sm btn-primary">Go to
                    Dashboard</a>
                </li>
              </ul>
            </li>
            <li>
              <hr class="mx-3" />
            </li>
            <li>
              <a class="dropdown-item" href="<?= BASE_URL ?>pages/sign-in.html">Sign In</a>
            </li>
            <li>
              <a class="dropdown-item" href="<?= BASE_URL ?>pages/sign-up.html">Sign Up</a>
            </li>
            <li>
              <a class="dropdown-item" href="<?= BASE_URL ?>pages/forget-password.html">Forgot Password</a>
            </li>
            <li>
              <a class="dropdown-item" href="<?= BASE_URL ?>pages/profile-edit.html">Edit Profile</a>
            </li>
            <li>
              <a class="dropdown-item" href="<?= BASE_URL ?>pages/security.html">Security</a>
            </li>
            <li>
              <a class="dropdown-item" href="<?= BASE_URL ?>pages/social-profile.html">Social Profiles</a>
            </li>
            <li>
              <a class="dropdown-item" href="<?= BASE_URL ?>pages/notifications.html">Notifications</a>
            </li>
            <li>
              <a class="dropdown-item" href="<?= BASE_URL ?>pages/profile-privacy.html">Privacy Settings</a>
            </li>
            <li>
              <a class="dropdown-item" href="<?= BASE_URL ?>pages/delete-profile.html">Delete Profile</a>
            </li>
            <li>
              <a class="dropdown-item" href="<?= BASE_URL ?>pages/linked-accounts.html">Linked Accounts</a>
            </li>
          </ul>
        </li>
        <li class="nav-item dropdown">
          <a class="nav-link" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-haspopup="true"
            aria-expanded="false">
            <i class="fe fe-more-horizontal"></i>
          </a>
          <div class="dropdown-menu dropdown-menu-md" aria-labelledby="navbarDropdown">
            <div class="list-group">
              <a class="list-group-item list-group-item-action border-0" href="<?= BASE_URL ?>docs/index.html">
                <div class="d-flex align-items-center">
                  <i class="fe fe-file-text fs-3 text-primary"></i>
                  <div class="ms-3">
                    <h5 class="mb-0">Documentations</h5>
                    <p class="mb-0 fs-6">Browse the all documentation</p>
                  </div>
                </div>
              </a>
              <a class="list-group-item list-group-item-action border-0"
                href="<?= BASE_URL ?>docs/bootstrap-5-snippets.html">
                <div class="d-flex align-items-center">
                  <i class="bi bi-files fs-3 text-primary"></i>
                  <div class="ms-3">
                    <h5 class="mb-0">Snippet</h5>
                    <p class="mb-0 fs-6">Bunch of Snippet</p>
                  </div>
                </div>
              </a>
              <a class="list-group-item list-group-item-action border-0" href="<?= BASE_URL ?>docs/changelog.html">
                <div class="d-flex align-items-center">
                  <i class="fe fe-layers fs-3 text-primary"></i>
                  <div class="ms-3">
                    <h5 class="mb-0">
                      Changelog
                      <span class="text-primary ms-1" id="changelog"></span>
                    </h5>
                    <p class="mb-0 fs-6">See what's new</p>
                  </div>
                </div>
              </a>
              <a class="list-group-item list-group-item-action border-0"
                href="https://geeksui.codescandy.com/geeks-rtl/" target="_blank">
                <div class="d-flex align-items-center">
                  <i class="fe fe-toggle-right fs-3 text-primary"></i>
                  <div class="ms-3">
                    <h5 class="mb-0">RTL demo</h5>
                    <p class="mb-0 fs-6">RTL Pages</p>
                  </div>
                </div>
              </a>
            </div>
          </div>
        </li>
      </ul>
      <form class="mt-3 mt-lg-0 me-lg-5 d-flex align-items-center d-none">
        <span class="position-absolute ps-3 search-icon">
          <i class="fe fe-search"></i>
        </span>
        <label for="search" class="visually-hidden"></label>
        <input type="search" id="search" class="form-control ps-6" placeholder="Search Courses" />
      </form>
    </div>
  </div>
</nav>