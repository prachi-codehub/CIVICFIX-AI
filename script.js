const complaintForm = document.getElementById("complaintForm");


// ==========================================
// GEMINI IMAGE AI DATA
// ==========================================

let imageAIAnalysis = {
    category: "",
    priority: "",
    department: "",
    issue: "",
    recommendation: ""
};


// ==========================================
// AI ANALYSIS DATA
// ==========================================

function getAIAnalysis(title, description, selectedCategory) {

    const text =
        (title + " " + description).toLowerCase();

    let category =
        selectedCategory || "Other";

    let priority =
        "Medium";

    let department =
        "Municipal Corporation";

    let recommendation =
        "The complaint should be reviewed by the concerned civic authority.";


    // ==========================================
    // HIGH PRIORITY KEYWORDS
    // ==========================================

    const highPriorityWords = [

        "emergency",
        "danger",
        "dangerous",
        "accident",
        "fire",
        "burst",
        "major leakage",
        "severe leakage",
        "collapsed",
        "electric shock",
        "open manhole",
        "flood"

    ];


    const isHighPriority =
        highPriorityWords.some(function (word) {

            return text.includes(word);

        });


    // ==========================================
    // WATER
    // ==========================================

    if (

        text.includes("water pipeline") ||
        text.includes("pipeline burst") ||
        text.includes("water leakage") ||
        text.includes("water leak") ||
        text.includes("water supply") ||
        text.includes("water problem") ||
        text.includes("water shortage") ||
        text.includes("leakage") ||
        text.includes("pipeline") ||
        text.includes("tap water")

    ) {

        category =
            "Water";

        department =
            "Water Supply Department";

        recommendation =
            "The water supply department should inspect the pipeline or water supply issue promptly.";

    }


    // ==========================================
    // DRAINAGE
    // ==========================================

    else if (

        text.includes("drainage") ||
        text.includes("drain") ||
        text.includes("sewer") ||
        text.includes("blocked drain") ||
        text.includes("waterlogging") ||
        text.includes("manhole") ||
        text.includes("sewage")

    ) {

        category =
            "Drainage";

        department =
            "Drainage Department";

        recommendation =
            "The drainage department should inspect the blockage and clear the affected area.";

    }


    // ==========================================
    // ROAD
    // ==========================================

    else if (

        text.includes("pothole") ||
        text.includes("broken road") ||
        text.includes("road damage") ||
        text.includes("damaged road") ||
        text.includes("road is damaged") ||
        text.includes("road is broken") ||
        text.includes("road repair")

    ) {

        category =
            "Road";

        department =
            "Road & Transport Department";

        recommendation =
            "Immediate inspection of the damaged road is recommended to reduce safety risks.";

    }


    // ==========================================
    // GARBAGE
    // ==========================================

    else if (

        text.includes("garbage") ||
        text.includes("waste") ||
        text.includes("trash") ||
        text.includes("litter") ||
        text.includes("dustbin") ||
        text.includes("rubbish")

    ) {

        category =
            "Garbage";

        department =
            "Waste Management Department";

        recommendation =
            "The waste management team should inspect the location and arrange cleaning.";

    }


    // ==========================================
    // STREETLIGHT
    // ==========================================

    else if (

        text.includes("streetlight") ||
        text.includes("street light") ||
        text.includes("lamp") ||
        text.includes("dark road") ||
        text.includes("light not working") ||
        text.includes("street light not working")

    ) {

        category =
            "Streetlight";

        department =
            "Electrical Department";

        recommendation =
            "The electrical maintenance team should inspect and repair the streetlight.";

    }


    // ==========================================
    // PRIORITY DETECTION
    // ==========================================

    if (isHighPriority) {

        priority =
            "High";

    }

    else if (

        text.includes("broken") ||
        text.includes("damaged") ||
        text.includes("not working") ||
        text.includes("blocked") ||
        text.includes("dirty") ||
        text.includes("repair needed")

    ) {

        priority =
            "Medium";

    }

    else if (

        text.includes("cleaning") ||
        text.includes("small issue") ||
        text.includes("minor") ||
        text.includes("cosmetic")

    ) {

        priority =
            "Low";

    }

    else {

        if (

            category === "Road" ||
            category === "Water" ||
            category === "Drainage"

        ) {

            priority =
                "High";

        }

        else {

            priority =
                "Medium";

        }

    }


    // ==========================================
    // PRIORITY RECOMMENDATION
    // ==========================================

    if (priority === "High") {

        recommendation +=
            " Due to the urgency of this issue, quick action is recommended.";

    }

    else if (priority === "Low") {

        recommendation +=
            " The issue can be scheduled for routine civic maintenance.";

    }


    // ==========================================
    // RETURN AI RESULT
    // ==========================================

    return {

        category:
            category,

        priority:
            priority,

        department:
            department,

        recommendation:
            recommendation

    };

}


// ==========================================
// COMPLAINT FORM SUBMISSION
// ==========================================

if (complaintForm) {

    complaintForm.addEventListener(
        "submit",
        async function (event) {

            event.preventDefault();


            // ==========================================
            // GET FORM VALUES
            // ==========================================

            const issueTitle =
                document
                    .getElementById("issueTitle")
                    .value
                    .trim();


            const selectedCategory =
                document
                    .getElementById("category")
                    .value;


            const location =
                document
                    .getElementById("location")
                    .value
                    .trim();


            const description =
                document
                    .getElementById("description")
                    .value
                    .trim();


            // ==========================================
            // REQUIRED FIELD CHECK
            // ==========================================

            if (

                !issueTitle ||
                !selectedCategory ||
                !location ||
                !description

            ) {

                alert(
                    "Please fill all required fields."
                );

                return;

            }


            // ==========================================
            // NORMAL AI ANALYSIS
            // ==========================================

            const aiAnalysis =
                getAIAnalysis(
                    issueTitle,
                    description,
                    selectedCategory
                );


            let category =
                aiAnalysis.category;

            let priority =
                aiAnalysis.priority;

            let department =
                aiAnalysis.department;


            // ==========================================
            // USE GEMINI IMAGE AI RESULT
            // ==========================================

            if (imageAIAnalysis.category) {

                category =
                    imageAIAnalysis.category;

            }


            if (imageAIAnalysis.priority) {

                priority =
                    imageAIAnalysis.priority;

            }


            if (imageAIAnalysis.department) {

                department =
                    imageAIAnalysis.department;

            }


            // ==========================================
            // UPDATE CATEGORY DROPDOWN
            // ==========================================

            const categorySelect =
                document.getElementById("category");


            if (categorySelect) {

                categorySelect.value =
                    category;

            }


            // ==========================================
            // GENERATE COMPLAINT ID
            // ==========================================

            const complaintId =
                "CIVIC-" +
                Math.floor(
                    1000 +
                    Math.random() * 9000
                );


            // ==========================================
            // PREPARE DATA FOR PHP
            // ==========================================

            const formData =
                new FormData();


            formData.append(
                "complaint_id",
                complaintId
            );


            formData.append(
                "issue_title",
                issueTitle
            );


            formData.append(
                "category",
                category
            );


            formData.append(
                "location",
                location
            );


            formData.append(
                "description",
                description
            );


            formData.append(
                "department",
                department
            );


            formData.append(
                "priority",
                priority
            );


            // ==========================================
            // ADD COMPLAINT IMAGE
            // ==========================================

            const complaintImage =
                document.getElementById("complaintImage");


            if (
                complaintImage &&
                complaintImage.files.length > 0
            ) {

                formData.append(
                    "complaintImage",
                    complaintImage.files[0]
                );

                console.log(
                    "Image added:",
                    complaintImage.files[0].name
                );

            }


            // ==========================================
            // SEND TO PHP
            // ==========================================

            try {

                const response =
                    await fetch(
                        "submit_complaint.php",
                        {
                            method: "POST",
                            body: formData
                        }
                    );


                const result =
                    await response.text();


                console.log(
                    "PHP Response:",
                    result
                );


                // ==========================================
                // SUCCESS
                // ==========================================

                if (

                    result.includes(
                        "Complaint submitted successfully"
                    )

                ) {


                    // ==========================================
                    // SAVE FOR TRACKING
                    // ==========================================

                    const complaintData = {

                        complaintId:
                            complaintId,

                        issueTitle:
                            issueTitle,

                        category:
                            category,

                        location:
                            location,

                        description:
                            description,

                        department:
                            department,

                        priority:
                            priority,

                        status:
                            "Submitted",

                        aiAnalysis:
                            imageAIAnalysis.recommendation ||
                            aiAnalysis.recommendation

                    };


                    localStorage.setItem(
                        "latestComplaint",
                        JSON.stringify(
                            complaintData
                        )
                    );


                    // ==========================================
                    // DISPLAY SUCCESS RESULT
                    // ==========================================

                    const complaintResult =
                        document.getElementById(
                            "complaintResult"
                        );


                    if (complaintResult) {

                        complaintResult.innerHTML = `

                            <div class="result-card">

                                <span class="result-success">
                                    ✓ COMPLAINT SUBMITTED
                                </span>


                                <h2>
                                    Complaint Registered Successfully
                                </h2>


                                <p class="result-message">

                                    Your complaint has been analyzed
                                    and forwarded to the appropriate
                                    department.

                                </p>


                                <div class="result-id">

                                    <span>
                                        COMPLAINT ID
                                    </span>

                                    <strong>
                                        ${complaintId}
                                    </strong>

                                </div>


                                <div class="result-details">


                                    <div>

                                        <span>
                                            Issue
                                        </span>

                                        <strong>
                                            ${issueTitle}
                                        </strong>

                                    </div>


                                    <div>

                                        <span>
                                            Category
                                        </span>

                                        <strong>
                                            ${category}
                                        </strong>

                                    </div>


                                    <div>

                                        <span>
                                            Department
                                        </span>

                                        <strong>
                                            ${department}
                                        </strong>

                                    </div>


                                    <div>

                                        <span>
                                            Priority
                                        </span>

                                        <strong>
                                            ${priority}
                                        </strong>

                                    </div>


                                    <div>

                                        <span>
                                            Location
                                        </span>

                                        <strong>
                                            ${location}
                                        </strong>

                                    </div>


                                    <div>

                                        <span>
                                            Status
                                        </span>

                                        <strong class="submitted-status">

                                            Submitted

                                        </strong>

                                    </div>

                                </div>


                                <!-- AI MESSAGE -->

                                <div class="ai-message">

                                    <span>
                                        🤖
                                    </span>


                                    <div>

                                        <strong>
                                            AI Analysis
                                        </strong>


                                        <p>

                                            Complaint automatically
                                            routed to the
                                            ${department}.

                                        </p>


                                        <p>

                                            💡
                                            ${
                                                imageAIAnalysis.recommendation ||
                                                aiAnalysis.recommendation
                                            }

                                        </p>


                                    </div>

                                </div>


                                <!-- BUTTONS -->

                                <div class="result-buttons">


                                    <a href="track.html">

                                        Track Complaint

                                    </a>


                                    <button
                                        onclick="location.reload()"
                                    >

                                        Report Another Issue

                                    </button>


                                </div>


                            </div>

                        `;


                        complaintResult.scrollIntoView({

                            behavior:
                                "smooth"

                        });

                    }

                    else {

                        alert(
                            "Complaint submitted successfully! Complaint ID: " +
                            complaintId
                        );

                    }


                    // ==========================================
                    // CLEAR FORM
                    // ==========================================

                    complaintForm.reset();


                    imageAIAnalysis = {

                        category: "",
                        priority: "",
                        department: "",
                        issue: "",
                        recommendation: ""

                    };

                }


                // ==========================================
                // DUPLICATE COMPLAINT
                // ==========================================

                else if (

                    result.includes(
                        "Possible duplicate complaint found"
                    )

                ) {


                    const duplicateId =
                        result
                            .split(
                                "Existing Complaint ID:"
                            )[1]
                            ?.trim();


                    alert(

                        "⚠️ POSSIBLE DUPLICATE COMPLAINT\n\n" +

                        "A similar unresolved complaint already exists.\n\n" +

                        "Existing Complaint ID: " +

                        duplicateId +

                        "\n\n" +

                        "Please check the existing complaint before submitting again."

                    );


                    console.log(

                        "Duplicate Complaint:",

                        duplicateId

                    );

                }


                // ==========================================
                // OTHER SERVER ERROR
                // ==========================================

                else {

                    alert(
                        "Complaint could not be submitted."
                    );


                    console.log(
                        "Server Response:",
                        result
                    );

                }

            }


            // ==========================================
            // CONNECTION ERROR
            // ==========================================

            catch (error) {

                console.error(
                    "Error:",
                    error
                );


                alert(
                    "Unable to connect to PHP. Make sure XAMPP Apache is running."
                );

            }

        }
    );

}